<?php
namespace CsvMigrations\Utility;

use Cake\Controller\Component\FlashComponent;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\I18n\Time;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\View\View;
use CsvMigrations\Model\Entity\Import as ImportEntity;
use CsvMigrations\Model\Table\ImportsTable;
use League\Csv\Reader;

class Import
{
    /**
     * Supported mime types for uploaded import file.
     *
     * @var array
     */
    private $__supportedMimeTypes = [
        'application/csv',
        'application/octet-stream',
        'application/vnd.ms-excel',
        'application/x-csv',
        'text/comma-separated-values',
        'text/csv',
        'text/plain',
        'text/tab-separated-values',
        'text/x-comma-separated-values',
        'text/x-csv'
    ];

    /**
     * Ignored table columns, by name.
     *
     * @var array
     */
    private $__ignoreColumns = [
        'id',
        'created',
        'modified',
        'trashed'
    ];

    /**
     * Ignored table columns, by type.
     *
     * @var array
     */
    private $__ignoreColumnTypes = [];

    /**
     * Constructor method.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param \Cake\Http\ServerRequest $request Request instance
     * @param \Cake\Controller\Component\FlashComponent $flash Flash component
     * @return void
     */
    public function __construct(Table $table, ServerRequest $request, FlashComponent $flash)
    {
        $this->_table = $table;
        $this->_request = $request;
        $this->_flash = $flash;
    }

    /**
     * Import file upload logic.
     *
     * @return string
     */
    public function upload()
    {
        if (!$this->_validateUpload()) {
            return '';
        }

        return $this->_uploadFile();
    }

    /**
     * Create import record.
     *
     * @param \CsvMigrations\Model\Table\ImportsTable $table Table instance
     * @param \CsvMigrations\Model\Entity\Import $entity Import entity
     * @param string $filename Uploaded file name
     * @return bool
     */
    public function create(ImportsTable $table, ImportEntity $entity, $filename)
    {
        $modelName = $this->_request->getParam('controller');
        if ($this->_request->getParam('plugin')) {
            $modelName = $this->_request->getParam('plugin') . '.' . $modelName;
        }

        $data = [
            'filename' => $filename,
            'status' => $table::STATUS_PENDING,
            'model_name' => $modelName,
            'attempts' => 0
        ];

        $entity = $table->patchEntity($entity, $data);

        return $table->save($entity);
    }

    /**
     * Import results getter.
     *
     * @param \CsvMigrations\Model\Entity\Import $entity Import entity
     * @param array $columns Display columns
     * @return \Cake\ORM\Query
     */
    public function getImportResults(ImportEntity $entity, array $columns)
    {
        $sortCol = $this->_request->query('order.0.column') ?: 0;
        $sortCol = array_key_exists($sortCol, $columns) ? $columns[$sortCol] : current($columns);

        $sortDir = $this->_request->query('order.0.dir') ?: 'asc';
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        $table = TableRegistry::get('CsvMigrations.ImportResults');

        $query = $table->find('all')
            ->where([$table->aliasField('import_id') => $entity->id])
            ->order([$table->aliasField($sortCol) => $sortDir]);

        return $query;
    }

    /**
     * Prepare import options by removing fields with empty mapping parameters.
     *
     * @param array $options Import options
     * @return array
     */
    public static function prepareOptions(array $options)
    {
        $result = [];

        if (empty($options['fields'])) {
            return null;
        }

        foreach ($options['fields'] as $field => $params) {
            if (empty($params['column']) && empty($params['default'])) {
                continue;
            }

            $result['fields'][$field] = $params;
        }

        return $result;
    }

    /**
     * Get CSV file rows count.
     *
     * @param \CsvMigrations\Model\Entity\Import $entity Import entity
     * @param bool $withHeader Include header row into the count
     * @return int
     */
    public static function getRowsCount(ImportEntity $entity, $withHeader = false)
    {
        $reader = Reader::createFromPath($entity->filename, 'r');

        $result = $reader->each(function ($row) {
            return true;
        });

        $result = (int)$result;

        if (!$withHeader) {
            $result = $result - 1;
        }

        return $result;
    }

    /**
     * Get upload file column headers (first row).
     *
     * @param \CsvMigrations\Model\Entity\Import $entity Import entity
     * @return array
     */
    public static function getUploadHeaders(ImportEntity $entity)
    {
        $reader = Reader::createFromPath($entity->filename, 'r');

        return $reader->fetchOne();
    }

    /**
     * Get target module fields.
     *
     * @return array
     */
    public function getTableColumns()
    {
        $schema = $this->_table->getSchema();

        $result = [];
        foreach ($schema->columns() as $column) {
            if (in_array($column, $this->__ignoreColumns)) {
                continue;
            }

            if (in_array($schema->columnType($column), $this->__ignoreColumnTypes)) {
                continue;
            }

            $result[] = $column;
        }

        return $result;
    }

    /**
     * Method that re-formats entities to Datatables supported format.
     *
     * @param \Cake\ORM\ResultSet $resultSet ResultSet
     * @param array $fields Display fields
     * @param \Cake\ORM\Table $table Table instance
     * @return array
     */
    public function toDatatables(ResultSet $resultSet, array $fields, Table $table)
    {
        $result = [];

        if ($resultSet->isEmpty()) {
            return $result;
        }

        $view = new View();
        $plugin = $this->_request->getParam('plugin');
        $controller = $this->_request->getParam('controller');

        foreach ($resultSet as $key => $entity) {
            foreach ($fields as $field) {
                $result[$key][] = $entity->get($field);
            }

            $viewButton = '';
            // set view button if model id is set
            if ($entity->get('model_id')) {
                $url = [
                    'prefix' => false,
                    'plugin' => $plugin,
                    'controller' => $controller,
                    'action' => 'view',
                    $entity->model_id
                ];
                $link = $view->Html->link('<i class="fa fa-eye"></i>', $url, [
                    'title' => __('View'),
                    'class' => 'btn btn-default',
                    'escape' => false
                ]);

                $viewButton = '<div class="btn-group btn-group-xs" role="group">' . $link . '</div>';
            }

            $result[$key][] = $viewButton;
        }

        return $result;
    }

    /**
     * Upload file validation.
     *
     * @return bool
     */
    protected function _validateUpload()
    {
        if (!$this->_request->data('file')) {
            $this->_flash->error(__('Please choose a file to upload.'));

            return false;
        }

        if (!in_array($this->_request->data('file.type'), $this->__supportedMimeTypes)) {
            $this->_flash->error(__('Unable to upload file, unsupported file provided.'));

            return false;
        }

        return true;
    }

    /**
     * Upload data file.
     *
     * @return string
     */
    protected function _uploadFile()
    {
        $uploadPath = $this->_getUploadPath();

        if (empty($uploadPath)) {
            return '';
        }

        $pathInfo = pathinfo($this->_request->data('file.name'));

        $filename = $pathInfo['filename'];
        // add current timestamp
        $time = new Time();
        $filename .= ' ' . $time->i18nFormat('yyyy-MM-dd HH:mm:ss');
        // add extensions
        $filename .= '.' . $pathInfo['extension'];

        $uploadPath .= $filename;

        if (!move_uploaded_file($this->_request->data('file.tmp_name'), $uploadPath)) {
            $this->_flash->error(__('Unable to upload file to the specified directory.'));

            return '';
        }

        return $uploadPath;
    }

    /**
     * Upload path getter.
     *
     * @return string
     */
    protected function _getUploadPath()
    {
        $result = Configure::read('Importer.path');

        // if no path specified, fallback to the default.
        if (!$result) {
            $result = WWW_ROOT . 'uploads' . DS;
        }

        // include trailing directory separator.
        $result = rtrim($result, DS);
        $result .= DS;

        if (file_exists($result)) {
            return $result;
        }

        // create upload path, recursively.
        if (!mkdir($result, 0777, true)) {
            $this->_flash->error(__('Failed to create upload directory.'));

            return '';
        }

        return $result;
    }
}

<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CsvMigrations\Utility;

use Cake\Controller\Component\FlashComponent;
use Cake\Core\Configure;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\Http\ServerRequest;
use Cake\I18n\Time;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\View\View;
use CsvMigrations\CsvMigration;
use CsvMigrations\Model\Entity\Import as ImportEntity;
use CsvMigrations\Model\Table\ImportResultsTable;
use CsvMigrations\Model\Table\ImportsTable;
use League\Csv\Reader;

class Import
{
    const PROCESSED_FILE_SUFFIX = '.processed';

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
     * @var \Cake\ORM\Table
     */
    private $table;

    /**
     * @var \Cake\Http\ServerRequest
     */
    private $request;

    /**
     * @var \Cake\Controller\Component\FlashComponent
     */
    private $flash;

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
        $this->table = $table;
        $this->request = $request;
        $this->flash = $flash;
    }

    /**
     * Processed filename getter.
     *
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @param bool $fullBase Full base flag
     * @return string
     */
    public static function getProcessedFile(ImportEntity $import, bool $fullBase = true) : string
    {
        $pathInfo = pathinfo($import->get('filename'));

        $result = $pathInfo['filename'] . static::PROCESSED_FILE_SUFFIX;

        if (!empty($pathInfo['extension'])) {
            $result .= '.' . $pathInfo['extension'];
        }

        if (!$fullBase) {
            return $result;
        }

        $result = $pathInfo['dirname'] . DS . $result;

        return $result;
    }

    /**
     * Import file upload logic.
     *
     * @return string
     */
    public function upload() : string
    {
        if (!$this->_validateUpload()) {
            return '';
        }

        $result = $this->_uploadFile();

        if ('' === $result) {
            $this->flash->error('Unable to upload file to the specified directory.');
        }

        return $result;
    }

    /**
     * Create import record.
     *
     * @param \CsvMigrations\Model\Table\ImportsTable $table Table instance
     * @param \CsvMigrations\Model\Entity\Import $entity Import entity
     * @param string $filename Uploaded file name
     * @return bool
     */
    public function create(ImportsTable $table, ImportEntity $entity, string $filename) : bool
    {
        $modelName = $this->request->getParam('controller');
        if ($this->request->getParam('plugin')) {
            $modelName = $this->request->getParam('plugin') . '.' . $modelName;
        }

        $data = [
            'filename' => $filename,
            'status' => $table::STATUS_PENDING,
            'model_name' => $modelName,
            'attempts' => 0
        ];

        $entity = $table->patchEntity($entity, $data);

        return (bool)$table->save($entity);
    }

    /**
     * Import results getter.
     *
     * @param \CsvMigrations\Model\Entity\Import $entity Import entity
     * @param string[] $columns Display columns
     * @return \Cake\Datasource\QueryInterface
     */
    public function getImportResults(ImportEntity $entity, array $columns) : QueryInterface
    {
        $sortCol = Hash::get($this->request->getQueryParams(), 'order.0.column', 0);
        $sortCol = array_key_exists($sortCol, $columns) ? $columns[$sortCol] : current($columns);

        $sortDir = Hash::get($this->request->getQueryParams(), 'order.0.dir', 'asc');
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
     * @param mixed[] $options Import options
     * @return mixed[]
     */
    public static function prepareOptions(array $options) : array
    {
        if (empty($options['fields'])) {
            return [];
        }

        $result = [];
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
     * @param string $path File path
     * @param bool $withHeader Include header row into the count
     * @return int
     */
    public static function getRowsCount(string $path, bool $withHeader = false) : int
    {
        $result = trim(exec("/usr/bin/env wc -l '" . $path . "'", $output, $return));
        if (0 === $return) {
            list($result, ) = explode(' ', $result);
            $result = (int)$result;
            if (0 < $result) {
                $result -= 1;
            }

            return $result;
        }

        $reader = Reader::createFromPath($path, 'r');

        $result = $reader->each(function ($row) {
            return true;
        });

        if (! $withHeader) {
            $result = $result - 1;
        }

        return $result;
    }

    /**
     * Get upload file column headers (first row).
     *
     * @param \CsvMigrations\Model\Entity\Import $entity Import entity
     * @return string[]
     */
    public static function getUploadHeaders(ImportEntity $entity) : array
    {
        $reader = Reader::createFromPath($entity->filename, 'r');

        return $reader->fetchOne();
    }

    /**
     * Get target module fields.
     *
     * @return string[]
     */
    public function getTableColumns() : array
    {
        $schema = $this->table->getSchema();

        $result = [];
        foreach ($schema->columns() as $column) {
            if (in_array($column, CsvMigration::getRequiredFields())) {
                continue;
            }

            $result[] = $column;
        }

        return $result;
    }

    /**
     * Method that re-formats entities to Datatables supported format.
     *
     * @param \Cake\Datasource\ResultSetInterface $resultSet ResultSet
     * @param string[] $fields Display fields
     * @return mixed[]
     */
    public static function toDatatables(ResultSetInterface $resultSet, array $fields) : array
    {
        $result = [];

        if ($resultSet->isEmpty()) {
            return $result;
        }

        foreach ($resultSet as $key => $entity) {
            foreach ($fields as $field) {
                $result[$key][] = $entity->get($field);
            }
        }

        return $result;
    }

    /**
     * Add action buttons to response data.
     *
     * @param \Cake\Datasource\ResultSetInterface $resultSet ResultSet
     * @param \Cake\ORM\Table $table Table instance
     * @param mixed[] $data Response data
     * @return mixed[]
     */
    public static function actionButtons(ResultSetInterface $resultSet, Table $table, array $data) : array
    {
        $view = new View();
        list($plugin, $controller) = pluginSplit($table->getRegistryAlias());

        foreach ($resultSet as $key => $entity) {
            if (!$entity->get('model_id')) {
                $data[$key][] = '';
                continue;
            }

            $url = [
                'prefix' => false,
                'plugin' => $plugin,
                'controller' => $controller,
                'action' => 'view',
                $entity->get('model_id')
            ];
            $link = $view->Html->link('<i class="fa fa-eye"></i>', $url, [
                'title' => __('View'),
                'class' => 'btn btn-default',
                'escape' => false
            ]);

            $html = '<div class="btn-group btn-group-xs" role="group">' . $link . '</div>';

            $data[$key][] = $html;
        }

        return $data;
    }

    /**
     * Response data status labels setter.
     *
     * @param mixed[] $data Response data
     * @param int $index Status column index
     * @return mixed[]
     */
    public static function setStatusLabels(array $data, int $index) : array
    {
        $view = new View();
        $statusLabels = [
            ImportResultsTable::STATUS_SUCCESS => 'success',
            ImportResultsTable::STATUS_PENDING => 'warning',
            ImportResultsTable::STATUS_FAIL => 'danger'
        ];
        foreach ($data as $key => $value) {
            $data[$key][$index] = $view->Html->tag('span', $value[$index], [
                'class' => 'label label-' . $statusLabels[$value[$index]]
            ]);
        }

        return $data;
    }

    /**
     * Upload file validation.
     *
     * @return bool
     */
    protected function _validateUpload() : bool
    {
        if (! $this->request->getData('file')) {
            $this->flash->error('Please choose a file to upload.');

            return false;
        }

        if (! in_array($this->request->getData('file.type'), $this->__supportedMimeTypes)) {
            $this->flash->error('Unable to upload file, unsupported file provided.');

            return false;
        }

        return true;
    }

    /**
     * Upload data file.
     *
     * @return string
     */
    protected function _uploadFile() : string
    {
        if (! is_string($this->request->getData('file.name'))) {
            return '';
        }

        if (! is_string($this->request->getData('file.tmp_name'))) {
            return '';
        }

        $uploadPath = $this->_getUploadPath();
        if ('' === $uploadPath) {
            return '';
        }

        $pathInfo = pathinfo($this->request->getData('file.name'));

        $filename = (string)preg_replace('/\W/', '_', $pathInfo['filename']);
        $filename = (string)preg_replace('/_+/', '_', $filename);
        $filename = trim($filename, '_');

        $extension = !empty($pathInfo['extension']) ? $pathInfo['extension'] : '';

        $path = sprintf(
            '%s%s_%s.%s',
            $uploadPath,
            (new Time())->i18nFormat('yyyyMMddHHmmss'),
            $filename,
            $extension
        );

        if (! move_uploaded_file($this->request->getData('file.tmp_name'), $path)) {
            return '';
        }

        return $path;
    }

    /**
     * Upload path getter.
     *
     * @return string
     */
    protected function _getUploadPath() : string
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
            $this->flash->error('Failed to create upload directory.');

            return '';
        }

        return $result;
    }
}

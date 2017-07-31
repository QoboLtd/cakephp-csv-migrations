<?php
namespace CsvMigrations\Controller\Traits;

use Cake\Core\Configure;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\View\View;
use CsvMigrations\Model\Entity\Import;
use League\Csv\Reader;
use Qobo\Utils\ModuleConfig\ModuleConfig;

trait ImportTrait
{
    private $__supportedExtensions = [
        'text/csv'
    ];

    private $__supportedTypes = [
        'string',
        'email',
        'text',
        'url',
        'reminder',
        'datetime',
        'date',
        'time'
    ];

    /**
     * Import action.
     *
     * @param string|null $id Import id
     * @return \Cake\Network\Response|void
     */
    public function import($id = null)
    {
        $table = TableRegistry::get('CsvMigrations.Imports');

        $entity = is_null($id) ? $table->newEntity() : $table->get($id);

        // AJAX logic
        if ($this->request->accepts('application/json')) {
            $columns = ['row_number', 'status', 'status_message'];
            $query = $this->_getImportResults($entity, $columns);

            $pagination = [
                'count' => $query->count()
            ];

            $this->set([
                'success' => true,
                'data' => $this->_toDatatables($this->paginate($query), $columns, $this->{$this->name}),
                'pagination' => $pagination,
                '_serialize' => ['success', 'data', 'pagination']
            ]);

            return;
        }

        // POST logic
        if ($this->request->is('post')) {
            if ($this->_upload($table, $entity)) {
                return $this->redirect([$entity->id]);
            }
        }

        // PUT logic
        if ($this->request->is('put')) {
            if ($this->_mapColumns($table, $entity)) {
                $this->_setImportResults($entity);

                return $this->redirect([$entity->id]);
            } else {
                $this->Flash->error(__('Unable to set import options.'));
            }
        }

        // GET logic
        if (!$entity->isNew()) {
            if (!$entity->get('options')) {
                $this->set('headers', $this->_getUploadHeaders($entity));
                $this->set('fields', $this->_getModuleFields());
            }
        }

        $this->set('import', $entity);

        if ($entity->isNew()) {
            $this->render('CsvMigrations.Import/upload');
        } else {
            if (!$entity->get('options')) {
                $this->render('CsvMigrations.Import/mapping');
            } else {
                $this->render('CsvMigrations.Import/progress');
            }
        }
    }

    /**
     * Import file upload logic.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param \CsvMigrations\Model\Entity\Import $entity Entity object
     * @return bool
     */
    protected function _upload(Table $table, Import $entity)
    {
        if (!$this->_validateUpload()) {
            return false;
        }

        $filename = $this->_uploadFile();
        if (empty($filename)) {
            return false;
        }

        $entity = $table->patchEntity($entity, ['filename' => $filename]);

        return $table->save($entity);
    }

    /**
     * Map import file columns to database columns.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param \CsvMigrations\Model\Entity\Import $entity Entity object
     * @return bool
     */
    protected function _mapColumns(Table $table, Import $entity)
    {
        $data = $this->request->data;

        $options = [];
        foreach ($data as $k => $v) {
            if (!empty(trim($v))) {
                $options[$k] = $v;
            }
        }

        $entity = $table->patchEntity($entity, ['options' => $options]);

        return $table->save($entity);
    }

    /**
     * Import results getter.
     *
     * @param \CsvMigrations\Model\Entity\Import $entity Entity object
     * @param array $columns Display columns
     * @return \Cake\ORM\Query
     */
    protected function _getImportResults(Import $entity, array $columns)
    {
        $sortCol = $this->request->query('order.0.column') ?: 0;
        $sortCol = array_key_exists($sortCol, $columns) ? $columns[$sortCol] : current($columns);

        $sortDir = $this->request->query('order.0.dir') ?: 'asc';
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
     * Import results setter.
     *
     * @param \CsvMigrations\Model\Entity\Import $import Entity object
     * @return void
     */
    protected function _setImportResults(Import $import)
    {
        $count = $this->_getRowsCount($import);

        if (0 >= $count) {
            return;
        }

        $table = TableRegistry::get('CsvMigrations.ImportResults');

        $modelName = $this->name;
        if ($this->plugin) {
            $modelName = $this->plugin . '.' . $modelName;
        }

        $data = [
            'import_id' => $import->id,
            'status' => $table->getStatusPending(),
            'status_message' => $table->getStatusPendingMessage(),
            'model_name' => $modelName
        ];

        // set $i = 1 to skip header row
        for ($i = 1; $i < $count; $i++) {
            $data['row_number'] = $i;

            $entity = $table->newEntity();
            $entity = $table->patchEntity($entity, $data);

            $table->save($entity);
        }
    }

    /**
     * Upload file validation.
     *
     * @return bool
     */
    protected function _validateUpload()
    {
        $data = $this->request->data;

        if (empty($data['file'])) {
            $this->Flash->error(__('Please choose a file to upload.'));

            return false;
        }

        if (!in_array($data['file']['type'], $this->__supportedExtensions)) {
            $this->Flash->error(__('Unable to upload file, unsupported file provided.'));

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
        $data = $this->request->data;

        $uploadPath = $this->_getUploadPath();

        if (empty($uploadPath)) {
            return '';
        }

        $uploadPath .= $data['file']['name'];

        if (!move_uploaded_file($data['file']['tmp_name'], $uploadPath)) {
            $this->Flash->error(__('Unable to upload file to the specified directory.'));

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
            $this->Flash->error(__('Failed to create upload directory.'));

            return '';
        }

        return $result;
    }

    /**
     * Get upload file column headers (first row)
     * @param Import $entity [description]
     * @return [type] [description]
     */
    protected function _getUploadHeaders(Import $entity)
    {
        $reader = Reader::createFromPath($entity->filename, 'r');

        $result = $reader->fetchOne();

        foreach ($result as $k => $v) {
            $v = str_replace(' ', '', trim($v));
            $result[$k] = Inflector::underscore($v);
        }

        return $result;
    }

    /**
     * Get CSV file rows count.
     *
     * @param \CsvMigrations\Model\Entity\Import $entity Entity object
     * @return int
     */
    protected function _getRowsCount(Import $entity)
    {
        $reader = Reader::createFromPath($entity->filename, 'r');

        $result = $reader->each(function ($row) {
            return true;
        });

        return (int)$result;
    }

    /**
     * Get target module fields.
     *
     * @return array
     */
    protected function _getModuleFields()
    {
        $mc = new ModuleConfig(ModuleConfig::CONFIG_TYPE_MIGRATION, $this->name);

        $result = [];
        foreach ($mc->parse() as $field) {
            if (!in_array($field->type, $this->__supportedTypes)) {
                continue;
            }

            $result[$field->name] = $field->name;
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
    protected function _toDatatables(ResultSet $resultSet, array $fields, Table $table)
    {
        $result = [];

        if ($resultSet->isEmpty()) {
            return $result;
        }

        $view = new View();
        list($plugin, $controller) = pluginSplit($this->{$this->name}->getRegistryAlias());

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
}

<?php
namespace CsvMigrations\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\ORM\Association;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

/**
 * CsvView component
 */
class CsvViewComponent extends Component
{

    /**
     * Associated fields action name.
     */
    const ASSOC_FIELDS_ACTION = 'index';

    /**
     * Count of fields per row for panel logic
     */
    const PANEL_COUNT = 3;

    /**
     * Default configuration.
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Current request's table instance.
     * @var \Cake\ORM\Table
     */
    protected $_tableInstance;

    /**
     * Current request's controller instance.
     * @var \Cake\Controller\Controller
     */
    protected $_controllerInstance;

    /**
     * Actions to pass associated records to.
     * @var array
     */
    protected $_assocActions = ['view'];

    /**
     * Supported association types.
     * @var array
     */
    protected $_assocTypes = ['oneToMany', 'manyToOne', 'manyToMany'];

    /**
     * Actions to arrange fields into panels.
     * @var array
     */
    protected $_panelActions = ['add', 'edit', 'view'];

    /**
     * Error messages.
     * @var array
     */
    protected $_errorMessages = [
        '_arrangePanels' => 'Field parameters count [%s] does not match required parameters count [%s]'
    ];

    /**
     * Called before the controller action. You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     * @link http://book.cakephp.org/3.0/en/controllers.html#request-life-cycle-callbacks
     */
    public function beforeFilter(Event $event)
    {
        $this->_controllerInstance = $event->subject();
        $this->_setTableInstance($this->_controllerInstance->request->params);

        if (in_array($this->request->params['action'], $this->_assocActions)) {
            // associated records
            $this->_controllerInstance->set('csvAssociatedRecords', $this->_setAssociatedRecords());
            $this->_controllerInstance->set('_serialize', ['csvAssociatedRecords']);
        }

        $path = Configure::readOrFail('CsvMigrations.views.path');
        $this->_setTableFields($path);
    }

    /**
     * Method that instantiates Table based on request parameters.
     *
     * @param  array  $params  Request parameters
     * @return \Cake\ORM\Table
     */
    protected function _setTableInstance(array $params)
    {
        $table = $params['controller'];
        if (!is_null($params['plugin'])) {
            $table = $params['plugin'] . '.' . $table;
        }

        $this->_tableInstance = TableRegistry::get($table);

        return $this->_tableInstance;
    }

    /**
     * Method that retrieves specified Table's
     * associated records and passes them to the View.
     *
     * @return array
     */
    protected function _setAssociatedRecords()
    {
        $result = [];
        // loop through associations
        foreach ($this->_tableInstance->associations() as $association) {
            $assocType = $association->type();
            if (in_array($assocType, $this->_assocTypes)) {
                // get associated records
                switch ($assocType) {
                    case 'manyToOne':
                        $result[$assocType][$association->foreignKey()] = $this->_manyToOneAssociatedRecords(
                            $association
                        );
                        break;

                    case 'oneToMany':
                        $result[$assocType][$association->name()] = $this->_oneToManyAssociatedRecords(
                            $association
                        );
                        break;

                    case 'manyToMany':
                        $result[$assocType][$association->name()] = $this->_manyToManyAssociatedRecords(
                            $association
                        );
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * Method that retrieves many to one associated records.
     *
     * @param  \Cake\ORM\Association $association Association object
     * @return array                              associated records
     */
    protected function _manyToOneAssociatedRecords(Association $association)
    {
        $result = [];
        $tableName = $this->_tableInstance->table();
        $primaryKey = $this->_tableInstance->primaryKey();
        $assocTableName = $association->table();
        $assocPrimaryKey = $association->primaryKey();
        $assocForeignKey = $association->foreignKey();
        $recordId = $this->request->params['pass'][0];
        $displayField = $association->displayField();

        /**
         * skip inverse relationship
         *
         * @todo find better way to handle it
         */
        if ($tableName === $assocTableName) {
            return $result;
        }

        $connection = ConnectionManager::get('default');
        $records = $connection
            ->execute(
                'SELECT ' . $assocTableName . '.' . $displayField . ' FROM ' . $tableName . ' LEFT JOIN ' . $assocTableName . ' ON ' . $tableName . '.' . $assocForeignKey . ' = ' . $assocTableName . '.' . $assocPrimaryKey . ' WHERE ' . $tableName . '.' . $primaryKey . ' = :id LIMIT 1',
                ['id' => $recordId]
            )
            ->fetchAll('assoc');

        // store associated table records
        $result = $records[0][$displayField];

        return $result;
    }

    /**
     * Method that retrieves one to many associated records
     *
     * @param  \Cake\ORM\Association $association Association object
     * @return array                              associated records
     */
    protected function _oneToManyAssociatedRecords(Association $association)
    {
        $assocName = $association->name();
        $assocTableName = $association->table();
        $assocForeignKey = $association->foreignKey();
        $recordId = $this->request->params['pass'][0];

        // get associated index View csv fields
        $fields = array_unique(
            array_merge(
                [$association->displayField()],
                $this->_getAssociationCsvFields($association, static::ASSOC_FIELDS_ACTION)
            )
        );

        $query = $this->_tableInstance->{$assocName}->find('all', [
            'conditions' => [$assocForeignKey => $recordId]
        ]);
        $records = $query->all();
        // store association name
        $result['assoc_name'] = $assocName;
        // store associated table name
        $result['table_name'] = $assocTableName;
        // store associated table class name
        $result['class_name'] = $association->className();
        // store associated table display field
        $result['display_field'] =  $association->displayField();
        // store associated table primary key
        $result['primary_key'] =  $association->primaryKey();
        // store associated table foreign key
        $result['foreign_key'] =  $association->foreignKey();
        // store associated table fields
        $result['fields'] = $fields;
        // store associated table records
        $result['records'] = $records;

        return $result;
    }

    /**
     * Method that retrieves many to many associated records
     *
     * @param  \Cake\ORM\Association $association Association object
     * @return array                              associated records
     * @todo  find better way to fetch associated data, without including current table's data
     */
    protected function _manyToManyAssociatedRecords(Association $association)
    {
        $assocName = $association->name();
        $assocTableName = $association->table();
        $assocForeignKey = $association->foreignKey();

        // get associated index View csv fields
        $fields = array_unique(
            array_merge(
                [$association->displayField()],
                $this->_getAssociationCsvFields($association, static::ASSOC_FIELDS_ACTION)
            )
        );
        $query = $this->_tableInstance->find('all', [
            'conditions' => [$this->_tableInstance->primaryKey() => $this->request->params['pass'][0]],
            'contain' => [
                $assocName
            ]
        ]);
        $records = $query->first()->{$assocTableName};
        // store association name
        $result['assoc_name'] = $assocName;
        // store associated table name
        $result['table_name'] = $assocTableName;
        // store associated table class name
        $result['class_name'] = $association->className();
        // store associated table display field
        $result['display_field'] =  $association->displayField();
        // store associated table primary key
        $result['primary_key'] =  $association->primaryKey();
        // store associated table foreign key
        $result['foreign_key'] =  Inflector::singularize($assocTableName) . '_' . $association->primaryKey();
        // store associated table fields
        $result['fields'] = $fields;
        // store associated table records
        $result['records'] = $records;

        return $result;
    }

    /**
     * Method that retrieves associated table csv fields, by specified action.
     *
     * @param  \Cake\ORM\Association $association Association
     * @param  string                $action      Action name
     * @return array                              table fields
     */
    protected function _getAssociationCsvFields(Association $association, $action)
    {
        list($plugin, $controller) = pluginSplit($association->className());

        return $this->_getCsvFields($controller, $action);
    }

    /**
     * Method that retrieves table csv fields, by specified action.
     *
     * @param  string $tableName Table name
     * @param  string $action    Action name
     * @return array             table fields
     */
    protected function _getCsvFields($tableName, $action)
    {
        $path = Configure::readOrFail('CsvMigrations.views.path');
        $path .= $tableName . DS . $action . '.csv';

        $csvFields = $this->_getFieldsFromCsv($path);
        $result = array_map(function ($v) {
            return $v[0];
        }, $csvFields);

        return $result;
    }

    /**
     * Method that passes csv defined Table fields to the View
     * @param \Cake\Event\Event $event An Event instance
     * @param  string           $path  file path
     * @return void
     */
    protected function _setTableFields($path)
    {
        $result = [];
        if (file_exists($path)) {
            $path .= $this->request->controller . DS . $this->request->action . '.csv';
            $result = $this->_getFieldsFromCsv($path);
        }

        list($plugin, $model) = pluginSplit($this->_tableInstance->registryAlias());
        /*
        add plugin and model names to each of the fields
         */
        $result = $this->_setFieldPluginAndModel($result, $model, $plugin);

        /*
        If action requires panels, arrange the fields into the panels
         */
        if (in_array($this->request->action, $this->_panelActions)) {
            $result = $this->_arrangePanels($result);
        }
        $this->_controllerInstance->set('fields', $result);
        $this->_controllerInstance->set('_serialize', ['fields']);
    }

    /**
     * Method that gets fields from a csv file
     * @param  string $path   csv file path
     * @return array          csv data
     */
    protected function _getFieldsFromCsv($path)
    {
        $result = [];
        if (file_exists($path)) {
            $result = $this->_getCsvData($path);
        }

        return $result;
    }

    /**
     * Add plugin and model name for each of the csv fields.
     *
     * @param array  $data   csv data
     * @param string $model  model name
     * @param string $plugin plugin name
     * @return array         csv data
     */
    protected function _setFieldPluginAndModel($data, $model = null, $plugin = null)
    {
        foreach ($data as &$row) {
            foreach ($row as &$col) {
                $col = [
                    'plugin' => $plugin,
                    'model' => $model,
                    'name' => $col
                ];
            }
        }

        return $data;
    }

    /**
     * Method that retrieves csv file data.
     * @param  string $path csv file path
     * @return array        csv data
     * @todo this method should be moved to a Trait class as is used throught Csv Migrations and Csv Views plugins
     */
    protected function _getCsvData($path)
    {
        $result = [];
        if (file_exists($path)) {
            if (false !== ($handle = fopen($path, 'r'))) {
                $row = 0;
                while (false !== ($data = fgetcsv($handle, 0, ','))) {
                    // skip first row
                    if (0 === $row) {
                        $row++;
                        continue;
                    }
                    $result[] = $data;
                }
                fclose($handle);
            }
        }

        return $result;
    }

    /**
     * Method that arranges csv fetched fields into panels.
     *
     * @param  array  $data fields
     * @throws \RuntimeException when csv field parameters count does not match
     * @return array        fields arranged in panels
     */
    protected function _arrangePanels(array $data)
    {
        $result = [];

        foreach ($data as $fields) {
            $fieldCount = count($fields);
            if (static::PANEL_COUNT !== $fieldCount) {
                throw new \RuntimeException(
                    sprintf($this->_errorMessages[__FUNCTION__], $fieldCount, static::PANEL_COUNT)
                );

            }
            $panel = array_shift($fields);
            $result[$panel['name']][] = $fields;
        }

        return $result;
    }
}

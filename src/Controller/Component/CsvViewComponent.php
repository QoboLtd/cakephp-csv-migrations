<?php
namespace CsvMigrations\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

/**
 * CsvView component
 */
class CsvViewComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    const ASSOC_FIELDS_ACTION = 'index';

    /**
     * Actions to pass associated records to
     * @var array
     */
    protected $_assocActions = ['view'];

    /**
     * Count of fields per row for panel logic
     */
    const PANEL_COUNT = 3;

    /**
     * Actions to arrange fields into panels
     */
    protected $_panelActions = ['add', 'edit', 'view'];

    /**
     * Error messages
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
    public function beforeFilter(\Cake\Event\Event $event)
    {
        if (in_array($this->request->params['action'], $this->_assocActions)) {
            $controller = $event->subject();
            // associated records
            $controller->set(
                'csvAssociatedRecords',
                $this->_setAssociatedRecords(
                    $event,
                    ['oneToMany', 'manyToOne'],
                    TableRegistry::get($controller->name)
                )
            );
            $controller->set('_serialize', ['csvAssociatedRecords']);
        }

        $path = Configure::readOrFail('CsvMigrations.views.path');
        $this->_setTableFields($event, $path);
    }

    /**
     * Method that retrieves specified Table's associated records and passes them to the View.
     * @param \Cake\Event\Event $event     An Event instance
     * @param array             $types     association type(s)
     * @param \Cake\ORM\Table   $table     Table object
     * @return array                       associated records
     */
    protected function _setAssociatedRecords(\Cake\Event\Event $event, array $types, \Cake\ORM\Table $table = null)
    {
        // if not provided, get Table object from current controller
        if (is_null($table)) {
            $table = TableRegistry::get($event->subject()->name);
        }

        $result = [];
        // loop through associations
        foreach ($table->associations() as $association) {
            $assocType = $association->type();
            if (in_array($assocType, $types)) {
                // get associated records
                switch ($association->type()) {
                    case 'manyToOne':
                        $result[$assocType][$association->foreignKey()] = $this->_manyToOneAssociatedRecords(
                            $table,
                            $association
                        );
                        break;

                    case 'oneToMany':
                        $result[$assocType][$association->name()] = $this->_oneToManyAssociatedRecords(
                            $table,
                            $association
                        );
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * Method that retrieves many to one associated records
     * @param  \Cake\ORM\Table       $table       Table object
     * @param  \Cake\ORM\Association $association Association object
     * @return array                              associated records
     */
    protected function _manyToOneAssociatedRecords(\Cake\ORM\Table $table, \Cake\ORM\Association $association)
    {
        $tableName = $table->table();
        $primaryKey = $table->primaryKey();
        $assocTableName = $association->table();
        $assocPrimaryKey = $association->primaryKey();
        $assocForeignKey = $association->foreignKey();
        $recordId = $this->request->params['pass'][0];
        $displayField = $association->displayField();

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
     * @param  \Cake\ORM\Table       $table       Table object
     * @param  \Cake\ORM\Association $association Association object
     * @return array                              associated records
     */
    protected function _oneToManyAssociatedRecords(\Cake\ORM\Table $table, \Cake\ORM\Association $association)
    {
        $assocName = $association->name();
        $assocTableName = $association->table();
        $assocForeignKey = $association->foreignKey();
        $recordId = $this->request->params['pass'][0];

        // get associated index View csv fields
        $fields = $this->_getTableFields($association);

        $query = $table->{$assocName}->find('all', [
            'conditions' => [$assocForeignKey => $recordId],
            'fields' => $fields
        ]);
        $records = $query->all();
        // store associated table records
        $result['records'] = $records;
        // store associated table fields
        $result['fields'] = $fields;
        // store associated table name
        $result['table_name'] = $assocTableName;

        return $result;
    }

    /**
     * Method that retrieves table fields defined
     * in the csv file, based on specified action
     * @param  object $table  Table object
     * @param  string $action action name
     * @return array          table fields
     */
    protected function _getTableFields($table, $action = '')
    {
        $tableName = $table->table();
        if ('' === trim($action)) {
            $action = static::ASSOC_FIELDS_ACTION;
        }

        $path = Configure::readOrFail('CsvMigrations.views.path');
        $path .= Inflector::camelize($tableName) . DS . $action . '.csv';

        $result = $this->_getFieldsFromCsv($path, $action);
        $result = array_map(function ($v) {
            return $v[0];
        }, $result);

        return $result;
    }

    /**
     * Method that passes csv defined Table fields to the View
     * @param \Cake\Event\Event $event An Event instance
     * @param  string           $path  file path
     * @return void
     */
    protected function _setTableFields(\Cake\Event\Event $event, $path)
    {
        $result = [];
        if (file_exists($path)) {
            $controller = $event->subject();
            $result = $this->_getFieldsFromCsv(
                $path . $this->request->controller . DS . $this->request->params['action'] . '.csv'
            );
        }

        $controller->set('fields', $result);
        $controller->set('_serialize', ['fields']);
    }

    /**
     * Method that gets fields from a csv file
     * @param  string $path   csv file path
     * @param  string $action action name
     * @return array          csv data
     */
    protected function _getFieldsFromCsv($path, $action = '')
    {
        if ('' === trim($action)) {
            $action = $this->request->params['action'];
        }
        $result = [];
        if (file_exists($path)) {
            $result = $this->_getCsvData($path);
            if (in_array($action, $this->_panelActions)) {
                $result = $this->_arrangePanels($result);
            }
        }

        return $result;
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
            $result[$panel][] = $fields;
        }

        return $result;
    }
}

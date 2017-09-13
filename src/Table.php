<?php
namespace CsvMigrations;

use ArrayObject;
use Cake\Core\App;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Association;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table as BaseTable;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\Event\EventName;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\FieldTrait;
use CsvMigrations\MigrationTrait;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * CsvMigrations Table
 *
 * All CSV modules should extend this Table
 * class for configuration and functionality.
 */
class Table extends BaseTable
{
    use ConfigurationTrait;
    use FieldTrait;
    use MigrationTrait;

    /* @var array $_currentUser to store user session */
    protected $_currentUser;

    /**
     * Mapping of association name to method name
     *
     * @var array
     */
    protected $_associationsMap = [
        'manyToMany' => 'getManyToManyAssociatedRecords',
        'oneToMany' => 'getOneToManyAssociatedRecords'
    ];

    /**
     * setCurrentUser
     *
     * @param array $user from Cake\Controller\Component\AuthComponent
     * @return array $_currentUser
     */
    public function setCurrentUser($user)
    {
        $this->_currentUser = $user;

        return $this->_currentUser;
    }

    /**
     * getCurrentUser
     *
     * @return array $_currentUser property
     */
    public function getCurrentUser()
    {
        return $this->_currentUser;
    }

    /**
     * Initialize
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->addBehavior('Muffin/Trash.Trash');

        // set table/module configuration
        $this->setConfig($this->table());

        //Set the current module
        $config['table'] = $this->_currentTable();

        $this->_setAssociations($config);
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        $user = $this->getCurrentUser();

        if (empty($user['id'])) {
            return;
        }

        $entity->set('modified_by', $user['id']);
        if ($entity->isNew()) {
            $entity->set('created_by', $user['id']);
        }
    }

    /**
     * afterSave hook
     *
     * @param Cake\Event $event from the parent afterSave
     * @param EntityInterface $entity from the parent afterSave
     * @param ArrayObject $options from the parent afterSave
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        $ev = new Event(
            (string)EventName::MODEL_AFTER_SAVE(),
            $this,
            ['entity' => $entity, 'options' => $options]
        );

        EventManager::instance()->dispatch($ev);
    }

    /**
     * getParentRedirectUrl
     *
     * Uses [parent] section of tables config.ini to define
     * where to redirect after the entity was added/edited.
     * @param Cake\ORM\Table $table of the entity table
     * @param Cake\ORM\Entity $entity of the actual table.
     *
     * @return array $result containing Cake-standard array for redirect.
     */
    public function getParentRedirectUrl($table, $entity = [])
    {
        $result = [];

        if (!method_exists($table, 'getConfig') && !is_callable([$table, 'getConfig'])) {
            return $result;
        }

        $parent = (array)$table->getConfig(ConfigurationTrait::$CONFIG_OPTION_PARENT);
        if (empty($parent)) {
            return $result;
        }

        $module = isset($parent['module']) ? $parent['module'] : '';
        $redirect = isset($parent['redirect']) ? $parent['redirect'] : '';
        $relation = isset($parent['relation']) ? $parent['relation'] : '';

        if (empty($redirect)) {
            return $result;
        }

        // For parent and self redirects, we have to have the
        // actual entity.  So, if it's missing, we don't need
        // to continue.
        if (empty($entity)) {
            return $result;
        }

        if ($redirect == 'parent') {
            $result = [
                'controller' => $module,
                'action' => 'view',
                $entity->{$relation}
            ];
        }

        if ($redirect == 'self') {
            $result = ['action' => 'view', $entity->id];
        }

        return $result;
    }

    /**
     * getReminderTypeFields
     *
     * @return array $result containing reminder fieldnames
     */
    public function getReminderFields()
    {
        $result = [];
        foreach ($this->getFieldsDefinitions() as $field) {
            if ($field['type'] == 'reminder') {
                $result[] = $field;
            }
        }

        return $result;
    }

    /**
     * Get Related entities for record view
     *
     * Fetch entities in the view.ctp
     *
     *
     * @param \Cake\ORM\Table $originTable entity of associated table
     * @param \Cake\Network\Request $request from the controller
     * @param array $data with the association configs
     * @param array $user with the user session
     *
     * @return array $response containing data for DataTables AJAX call
     */
    public function getRelatedEntities($originTable, $request, array $data = [], array $user = [])
    {
        $response = [];
        $tableName = Inflector::camelize($data['originTable']);

        $association = $this->getAssociationObject($tableName, $data['associationName']);

        if (!in_array($association->type(), array_keys($this->_associationsMap))) {
            return $response;
        }

        $fh = new FieldHandlerFactory();
        $entities = $this->{$this->_associationsMap[$association->type()]}($originTable, $association, $data);

        if (empty($entities['records'])) {
            return $response;
        }

        $responseData = [];

        if ($data['format'] == 'datatables' && !empty($entities['records'])) {
            $assocTable = TableRegistry::get($entities['table_name']);

            foreach ($entities['records'] as $record) {
                $item = [];
                foreach ($entities['fields'] as $fieldName) {
                    $item[] = $fh->renderValue($assocTable, $fieldName, $record->$fieldName, [
                        'entity' => $record,
                    ]);
                }

                if ($data['menus'] == true) {
                    $appView = new \Cake\View\View();
                    $item[] = $appView->element('CsvMigrations.Menu/related_actions', [
                        'options' => $data,
                        'entity' => $record,
                        'user' => $user,
                    ]);
                }

                array_push($responseData, $item);
            }
        }

        $response['data'] = $responseData;

        $response = array_merge($response, $entities['pagination']);

        return $response;
    }

    /**
     * Method that retrieves many to many associated records
     *
     * @param \Cake\ORM\Table $table object for Query Object
     * @param \Cake\ORM\Association $association Association object
     * @param array $data with request configs
     *
     * @return array associated records
     */
    public function getManyToManyAssociatedRecords($table, Association $association, array $data = [])
    {
        $result = [];
        $assocName = $association->name();
        $assocTableName = $association->table();
        $assocForeignKey = $association->foreignKey();

        $csvFields = $this->_getAssociationCsvFields($association, 'index');

        if (empty($csvFields)) {
            return $result;
        }
        // get associated index View csv fields
        $fields = array_unique(
            array_merge(
                [$association->displayField()],
                $csvFields
            )
        );

        $assocTableName = Inflector::camelize($assocTableName);
        $assocTableObject = TableRegistry::get($assocTableName);

        // @NOTE: fields should be properly indexed
        // to collide with 'columns' indexes
        $fields = array_values($fields);
        $conditions = $this->getRelatedEntitiesOrder($assocTableObject, $fields, $data);

        $id = $data['id'];
        $primaryKey = $table->aliasField($table->getPrimaryKey());

        $limit = (!empty($data['limit']) ? $data['limit'] : 10);
        $offset = (!empty($data['start']) ? $data['start'] : 0);

        $tableAlias = $table->registryAlias();

        $countQuery = $assocTableObject->find();
        $countQuery->select(['count' => $countQuery->func()->count('*')]);
        $countQuery->matching($tableAlias, function ($q) use ($primaryKey, $id) {
            return $q->where([$primaryKey => $id]);
        });

        $count = $countQuery->first();

        $query = $assocTableObject->find();
        $query->order($conditions);
        $query->limit($limit);

        if (!empty($offset)) {
            $query->offset($offset);
        }

        $query->matching($tableAlias, function ($q) use ($primaryKey, $id) {
            return $q->where([$primaryKey => $id]);
        });

        $result = $this->getAssociationFields($association);

        $result['pagination']['recordsFiltered'] = $query->count();
        $result['pagination']['recordsTotal'] = $count->count;
        $result['pagination']['count'] = $query->count();
        $result['records'] = $query->all();

        return $result;
    }

    /**
     * Method that retrieves one to many associated records
     *
     * @param \Cake\ORM\Table $table instance of the association.
     * @param \Cake\ORM\Association $association Association object
     * @param \Cake\Network\Request $request passed
     * @return array associated records
     */
    protected function getOneToManyAssociatedRecords($table, Association $association, array $data = [])
    {
        $result = [];

        $csvFields = $this->_getAssociationCsvFields($association, 'index');
        if (empty($csvFields)) {
            return $result;
        }

        // get associated index View csv fields
        $fields = array_unique(
            array_merge(
                [$association->displayField()],
                $csvFields
            )
        );

        $fields = array_values($fields);

        $assocTable = $association->target();

        $conditions = $this->getRelatedEntitiesOrder($assocTable, $fields, $data);

        $limit = (!empty($data['limit']) ? $data['limit'] : 10);
        $offset = (!empty($data['start']) ? $data['start'] : 0);

        $recordId = $data['id'];
        $assocForeignKey = $association->foreignKey();
        $aliasedForeignKey = $assocTable->aliasField($assocForeignKey);

        $countQuery = $assocTable->find();
        $countQuery->select(['count' => $countQuery->func()->count('*')]);
        $countQuery->where([$aliasedForeignKey => $recordId]);
        $count = $countQuery->first();

        $query = $assocTable->find();
        $query->where([$aliasedForeignKey => $recordId]);
        $query->limit($limit);
        $query->order($conditions);

        if (!empty($offset)) {
            $query->offset($offset);
        }

        $result = $this->getAssociationFields($association);

        $result['pagination']['recordsTotal'] = $count->count;
        $result['pagination']['recordsFiltered'] = $query->count();
        $result['pagination']['count'] = $query->count();
        $result['records'] = $query->all();

        return $result;
    }

    /**
     * Get Association Object
     *
     * Get the object based on the association's name
     *
     * @param string $tableName of the instance
     * @param string $associationName associations name
     *
     * @return \Cake\ORM\Association $result object
     */
    public function getAssociationObject($tableName, $associationName)
    {
        $result = null;
        $tableName = Inflector::camelize($tableName);

        $table = TableRegistry::get($tableName);

        foreach ($table->associations() as $association) {
            if ($association->name() != $associationName) {
                continue;
            }

            $result = $association;
            break;
        }

        return $result;
    }

    /**
     * Get Related Entities order array
     *
     * Based on the fields and data construct conditions array
     *
     * @param \Cake\ORM\Table $table instance to which conditions built
     * @param array $fields list used for the view
     * @param array $data received from the request
     *
     * @return array $conditions for the Query Object to order the results.
     */
    public function getRelatedEntitiesOrder($table, array $fields = [], array $data = [])
    {
        $conditions = [];

        $fieldOrder = $data['order'][0]['column'];

        $sortCol = $fields[$fieldOrder];
        $sortDir = $data['order'][0]['dir'];

        $sortCols = null;
        // handle virtual field
        if (method_exists($table, 'getConfig') && is_callable([$table, 'getConfig'])) {
            $virtualFields = $table->getConfig(ConfigurationTrait::$CONFIG_OPTION_VIRTUAL_FIELDS);
            if (!empty($virtualFields) && isset($virtualFields[$sortCol])) {
                $sortCols = $virtualFields[$sortCol];
            }
        }

        // in case no virtual fields for sorting,
        // use the given one.
        if (empty($sortCols)) {
            $sortCols = $sortCol;
        }

        $sortCols = (array)$sortCols;
        // prefix table name
        foreach ($sortCols as &$v) {
            $v = $table->aliasField($v);
        }

        // add sort direction to all columns
        $conditions = array_fill_keys($sortCols, $sortDir);

        return $conditions;
    }

    /**
     * Get Association Fields
     *
     * Pick association fields for index action
     *
     * @param \Cake\ORM\Association $association object
     * @param array $options to config fields
     *
     * @return array $result of the fields for action.
     */
    public function getAssociationFields(Association $association, array $options = [])
    {
        $result = [];

        $assocName = $association->name();
        $assocTableName = $association->table();

        $csvFields = $this->_getAssociationCsvFields($association, 'index');

        // get associated index View csv fields
        $fields = array_unique(
            array_merge(
                [$association->displayField()],
                $csvFields
            )
        );

        $result['table_name'] = $assocTableName;

        $result['class_name'] = $association->className();
        $result['display_field'] = $association->displayField();
        $result['primary_key'] = $association->primaryKey();

        if (!in_array($association->type(), ['manyToMany'])) {
            $result['foreign_key'] = $association->foreignKey();
        } else {
            $result['foreign_key'] = Inflector::singularize($assocTableName) . '_' . $association->primaryKey();
        }

        $result['fields'] = $fields;

        return $result;
    }

    /**
     * Get association CSV fields
     * @param Cake\ORM\Associations $association ORM association
     * @param object $action action passed
     * @return array
     */
    protected function _getAssociationCsvFields(Association $association, $action)
    {
        list($plugin, $controller) = pluginSplit($association->className());
        $fields = $this->_getCsvFields($controller, $action);

        return $fields;
    }

    /**
     * Method that fetches action fields from the corresponding csv file.
     *
     * @param  string $controller name of request's controller
     * @param  string $action  Action name
     * @return array
     */
    protected function _getActionFields($controller, $action = null)
    {
        if (is_null($action)) {
            $action = 'index';
        }

        $mc = new ModuleConfig(ConfigType::VIEW(), $controller, $action);
        $result = $mc->parse()->items;

        return $result;
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
        $result = [];

        if (empty($tableName) || empty($action)) {
            return $result;
        }

        $mc = new ModuleConfig(ConfigType::VIEW(), $tableName, $action);
        $csvFields = $mc->parse()->items;

        if (empty($csvFields)) {
            return $result;
        }

        $result = array_map(function ($v) {
            return $v[0];
        }, $csvFields);

        return $result;
    }

    /**
     * enablePrimaryKeyAccess
     *
     * Enable accessibility to associations primary key. Useful for
     * patching entities with associated data during updating process.
     *
     * @return array
     */
    public function enablePrimaryKeyAccess()
    {
        $result = [];
        foreach ($this->associations() as $association) {
            $result['associated'][$association->name()] = [
                'accessibleFields' => [$association->primaryKey() => true]
            ];
        }

        return $result;
    }

    /**
     * setAssociatedByLookupFields
     *
     * Method that checks Entity's association fields (foreign keys) values and query's the database to find
     * the associated record. If the record is not found, it query's the database again to find it by its
     * display field. If found it replaces the associated field's value with the records id.
     *
     * This is useful for cases where the display field value is used on the associated field. For example
     * a new post is created and in the 'owner' field the username of the user is used instead of its uuid.
     *
     * BEFORE:
     * {
     *    'title' => 'Lorem Ipsum',
     *    'content' => '.....',
     *    'owner' => 'admin',
     * }
     *
     * AFTER:
     * {
     *    'title' => 'Lorem Ipsum',
     *    'content' => '.....',
     *    'owner' => '77dd9203-3f21-4571-8843-0264ae1cfa48',
     * }
     *
     * @param \Cake\ORM\Entity $entity Entity instance
     * @param array $options Options
     * @return \Cake\ORM\Entity
     */
    public function setAssociatedByLookupFields(Entity $entity, $options = [])
    {
        foreach ($this->associations() as $association) {
            $lookupFields = [];
            if (method_exists($association->target(), 'getConfig')) {
                $lookupFields = (array)$association->target()->getConfig(ConfigurationTrait::$CONFIG_OPTION_LOOKUP_FIELDS);
            }

            if (empty($lookupFields)) {
                continue;
            }

            $value = $entity->{$association->foreignKey()};
            // skip if association's foreign key is NOT set in the entity
            if (is_null($value)) {
                continue;
            }

            // check if record can be fetched by primary key
            $found = (bool)$association->target()->find('all', [
                'conditions' => [$association->primaryKey() => $value]
            ])->count();

            // skip if record found by primary key
            if ($found) {
                continue;
            }

            // check if record can be fetched by display field
            $query = $association->target()->find()
                // select associated record's primary key (usually id)
                ->select($association->primaryKey());

            // check for record by table's lookup fields
            foreach ($lookupFields as $lookupField) {
                $query->orWhere([$lookupField => $value]);
            }

            $associatedEntity = $query->first();

            // skip if record cannot be found by display field
            if (is_null($associatedEntity)) {
                continue;
            }

            $entity->{$association->foreignKey()} = $associatedEntity->{$association->primaryKey()};
        }

        return $entity;
    }

    /**
     * findByLookupFieldsWithValues
     *
     * Method that adds lookup fields with the matching values to the Where clause in ORM Query
     *
     * @param  \Cake\ORM\Query $query  Query instance
     * @param  array           $values Entity lookup-fields values
     * @return \Cake\ORM\Query
     */
    public function findByLookupFieldsWithValues(Query $query, array $values)
    {
        $lookupFields = (array)$this->getConfig(ConfigurationTrait::$CONFIG_OPTION_LOOKUP_FIELDS);

        if (empty($lookupFields) || empty($values)) {
            return $query;
        }

        // check for record by table's lookup fields
        foreach ($lookupFields as $lookupField) {
            if (!isset($values[$lookupField])) {
                continue;
            }
            $query->orWhere([$lookupField => $values[$lookupField]]);
        }

        return $query;
    }

    /**
     * _currentTable
     *
     * Return current table in camelCase form.
     * It adds plugin name as a prefix.
     *
     * @return string Table Name along with its prefix if found.
     */
    protected function _currentTable()
    {
        return App::shortName(get_class($this), 'Model/Table', 'Table');
    }
}

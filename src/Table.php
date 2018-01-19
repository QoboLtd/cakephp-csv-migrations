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
use CsvMigrations\View\AppView;
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
     * @param \Cake\Event\Event $event from the parent afterSave
     * @param \Cake\Datasource\EntityInterface $entity from the parent afterSave
     * @param \ArrayObject $options from the parent afterSave
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        $options['current_user'] = $this->getCurrentUser();

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
     * @param \Cake\ORM\Table $table of the entity table
     * @param \Cake\ORM\Entity $entity of the actual table.
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
     * @param array $data with the association configs
     * @param array $user with the user session
     *
     * @return array $response containing data for DataTables AJAX call
     */
    public function getRelatedEntities(array $data = [], array $user = [])
    {
        $association = $this->getAssociationObject($data['associationName']);
        if (!$association) {
            return [];
        }

        if (!in_array($association->type(), array_keys($this->_associationsMap))) {
            return [];
        }

        $entities = $this->{$this->_associationsMap[$association->type()]}($association, $data);
        if (empty($entities['records'])) {
            return [];
        }

        if ('pretty' !== $data['format']) {
            $result = array_merge(['data' => $entities['records']], ['pagination' => $entities['pagination']]);

            return $result;
        }

        $cakeView = new AppView();
        $factory = new FieldHandlerFactory($cakeView);
        $table = TableRegistry::get($association->className());

        $result = [];
        foreach ($entities['records'] as $k => $entity) {
            foreach ($entities['fields'] as $field) {
                $result[$k][$field] = $factory->renderValue($table, $field, $entity->get($field), ['entity' => $entity]);
            }

            if ((bool)$data['menus']) {
                $result[$k]['_Menus'] = $cakeView->element('CsvMigrations.Menu/related_actions', [
                    'association' => $association,
                    'entity' => $entity,
                    'options' => $data,
                    'table' => $table,
                    'user' => $user
                ]);
            }
        }

        $result = array_merge(['data' => $result], ['pagination' => $entities['pagination']]);

        return $result;
    }

    /**
     * Method that retrieves many to many associated records
     *
     * @param \Cake\ORM\Association $association Association object
     * @param array $data with request configs
     *
     * @return array associated records
     */
    public function getManyToManyAssociatedRecords(Association $association, array $data = [])
    {
        $result = [];

        $result = $this->getAssociationFields($association, ['action' => 'index']);

        if (empty($result['fields'])) {
            return [];
        }
        $assocTable = TableRegistry::get(Inflector::camelize($association->table()));

        $conditions = $this->getRelatedEntitiesOrder($assocTable, $result['fields'], $data);

        $limit = (!empty($data['limit']) ? $data['limit'] : 10);
        $offset = (!empty($data['start']) ? $data['start'] : 0);

        $id = $data['id'];
        $tableAlias = $this->getRegistryAlias();
        $primaryKey = $this->aliasField($this->getPrimaryKey());

        $count = $this->getManyToManyCount($assocTable->find(), [
            'alias' => $tableAlias,
            'conditions' => [
                $primaryKey => $id,
            ],
        ]);

        $query = $assocTable->find();
        $query->matching($tableAlias, function ($q) use ($primaryKey, $id) {
            return $q->where([$primaryKey => $id]);
        });

        $query->order($conditions);
        $query->limit($limit);
        $query->offset($offset);

        $result['pagination']['recordsFiltered'] = $query->count();
        $result['pagination']['recordsTotal'] = $count;
        $result['pagination']['count'] = $query->count();
        $result['records'] = $query->all();

        return $result;
    }

    /**
     * Method that retrieves one to many associated records
     *
     * @param \Cake\ORM\Association $association Association object
     * @param array $data Data
     *
     * @return array associated records
     */
    protected function getOneToManyAssociatedRecords(Association $association, array $data = [])
    {
        $result = [];

        $result = $this->getAssociationFields($association, ['action' => 'index']);

        if (empty($result['fields'])) {
            return [];
        }

        $assocTable = $association->target();

        $conditions = $this->getRelatedEntitiesOrder($assocTable, $result['fields'], $data);

        $limit = (!empty($data['limit']) ? $data['limit'] : 10);
        $offset = (!empty($data['start']) ? $data['start'] : 0);

        $id = $data['id'];
        $foreignKey = $assocTable->aliasField($association->foreignKey());

        $count = $this->getOneToManyCount($assocTable->find(), [
            'conditions' => [
                $foreignKey => $id,
            ],
        ]);

        $query = $assocTable->find();
        $query->where([$foreignKey => $id]);

        $query->order($conditions);
        $query->limit($limit);
        $query->offset($offset);

        $result['pagination']['recordsTotal'] = $count;
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
     * @param string $associationName associations name
     *
     * @return \Cake\ORM\Association|null $result object
     */
    public function getAssociationObject($associationName)
    {
        $result = null;

        foreach ($this->associations() as $association) {
            if ($association->name() !== $associationName) {
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
        $action = (!empty($options['action']) ? $options['action'] : 'index');

        $fields = $this->getAssociationCsvFields($association, $action);

        $result['fields'] = $fields;
        $params = ['table', 'className', 'displayField', 'primaryKey'];

        foreach ($params as $param) {
            $underscored = Inflector::underscore($param);

            if (is_callable([$association, $param])) {
                $result[$underscored] = $association->{$param}();
            }
        }

        if (!in_array($association->type(), ['manyToMany'])) {
            $result['foreign_key'] = $association->foreignKey();
        } else {
            $result['foreign_key'] = Inflector::singularize($association->table()) . '_' . $association->primaryKey();
        }

        return $result;
    }

    /**
     * Get association CSV fields
     * @param \Cake\ORM\Association $association ORM association
     * @param object $action action passed
     * @return array
     */
    public function getAssociationCsvFields(Association $association, $action)
    {
        list($plugin, $controller) = pluginSplit($association->className());
        $csvFields = $this->getCsvFields($controller, $action);

        // NOTE: fields should be properly indexed to collide with 'columns' indexes
        return array_values($csvFields);
    }

    /**
     * Method that retrieves table csv fields, by specified action.
     *
     * @param  string $tableName Table name
     * @param  string $action    Action name
     * @return array             table fields
     */
    public function getCsvFields($tableName, $action)
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

    /**
     * Get One-to-Many Records count
     *
     * Used for DataTables records count based on associations
     *
     * @param \Cake\ORM\Query $query instance of target table
     * @param array $options with conditions.
     *
     * @return int $count containing records counted.
     */
    public function getOneToManyCount(Query $query, array $options = [])
    {
        $query->select(['count' => $query->func()->count('*')]);

        $query->where($options['conditions']);

        $count = $query->first();

        return $count->count;
    }

    /**
     * Get Many-to-Many Records count
     *
     * Used for DataTables records count based on associations
     *
     * @param \Cake\ORM\Query $query instance of target table
     * @param array $options with conditions.
     *
     * @return int $count containing records counted.
     */
    public function getManyToManyCount(Query $query, array $options = [])
    {
        $tableAlias = $options['alias'];
        $conditions = $options['conditions'];

        $query->select(['count' => $query->func()->count('*')]);

        $query->matching($tableAlias, function ($q) use ($conditions) {
            return $q->where($conditions);
        });

        $count = $query->first();

        return $count->count;
    }
}

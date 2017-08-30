<?php
namespace CsvMigrations;

use ArrayObject;
use Cake\Core\App;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table as BaseTable;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\Event\EventName;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldTrait;
use CsvMigrations\MigrationTrait;

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

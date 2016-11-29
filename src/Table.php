<?php
namespace CsvMigrations;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table as BaseTable;
use Cake\Utility\Inflector;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldTrait;
use CsvMigrations\ListTrait;
use CsvMigrations\MigrationTrait;

/**
 * Accounts Model
 *
 */
class Table extends BaseTable
{
    use ConfigurationTrait;
    use FieldTrait;
    use ListTrait;
    use MigrationTrait;

    /**
     * Searchable parameter name
     */
    const PARAM_NON_SEARCHABLE = 'non-searchable';

    /* @var array $_currentUser to store user session */
    protected $_currentUser;

    /**
     * setCurrentUser method
     * @param array $user from Cake\Controller\Component\AuthComponent
     * @return array $_currentUser
     */
    public function setCurrentUser($user)
    {
        $this->_currentUser = $user;

        return $this->_currentUser;
    }

    /**
     * getCurrentUser method
     * @return array $_currentUser property
     */
    public function getCurrentUser()
    {
        return $this->_currentUser;
    }

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->addBehavior('Muffin/Trash.Trash');

        // set table/module configuration
        $this->_setConfiguration($this->table());

        //Set the current module
        $config['table'] = $this->_currentTable();

        $this->_setAssociations($config);
    }

    /**
     * afterSave hook
     * @param Cake\Event $event from the parent afterSave
     * @param EntityInterface $entity from the parent afterSave
     * @param ArrayObject $options from the parent afterSave
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        $ev = new Event(
            'CsvMigrations.Model.afterSave',
            $this,
            ['entity' => $entity, 'options' => $options]
        );

        EventManager::instance()->dispatch($ev);
    }

    /**
     * getReminderTypeFields
     * @return array $result containing reminder fieldnames
     */
    public function getReminderFields()
    {
        $result = [];
        foreach ($this->getFieldsDefinitions($this->alias()) as $field) {
            if ($field['type'] == 'reminder') {
                $result[] = $field;
            }
        }

        return $result;
    }

    /**
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
     * @param  \Cake\ORM\Entity $entity Entity instance
     * @return \Cake\ORM\Entity
     */
    public function setAssociatedByLookupFields(Entity $entity, $options = [])
    {
        foreach ($this->associations() as $association) {
            $lookupFields = $association->target()->lookupFields();

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
     * Method that adds lookup fields with the matching values to the Where clause in ORM Query
     *
     * @param  \Cake\ORM\Query $query  Query instance
     * @param  array           $values Entity lookup-fields values
     * @return \Cake\ORM\Query
     */
    public function findByLookupFieldsWithValues(Query $query, array $values)
    {
        $lookupFields = $this->lookupFields();

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
     * Return current table in camelCase form.
     * It adds plugin name as a prefix.
     *
     * @return string Table Name along with its prefix if found.
     */
    protected function _currentTable()
    {
        list($namespace, $alias) = namespaceSplit(get_class($this));
        $alias = substr($alias, 0, -5);
        list($plugin) = explode('\\', $namespace);
        if ($plugin === 'App') {
            return Inflector::camelize($alias);
        }

        return Inflector::camelize($plugin . '.' . $alias);
    }
}

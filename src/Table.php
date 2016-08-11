<?php
namespace CsvMigrations;

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
    use ListTrait
    {
        ListTrait::_prepareCsvData insteadof MigrationTrait;
        ListTrait::_getCsvData insteadof MigrationTrait;
    }
    use MigrationTrait;

    /**
     * Searchable parameter name
     */
    const PARAM_NON_SEARCHABLE = 'non-searchable';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        // set table/module configuration
        $this->_setConfiguration($this->table());

        //Set the current module
        $config['table'] = $this->_currentTable();
        $this->_setAssociations($config);
    }

    /**
     * Get searchable fields
     *
     * @return array field names
     */
    public function getSearchableFields()
    {
        $result = [];
        foreach ($this->getFieldsDefinitions($this->alias()) as $field) {
            if (!$field[static::PARAM_NON_SEARCHABLE]) {
                $result[] = $field['name'];
            }
        }

        return $result;
    }

    /**
     * Returns searchable fields properties.
     *
     * @param  array $fields searchable fields
     * @return array
     */
    public function getSearchableFieldProperties(array $fields)
    {
        $result = [];

        if (empty($fields)) {
            return $result;
        }
        foreach ($this->getFieldsDefinitions($this->alias()) as $field => $definitions) {
            if (in_array($field, $fields)) {
                $csvField = new CsvField($definitions);
                $type = $csvField->getType();
                $result[$field] = [
                    'type' => $type
                ];
                if ('list' === $type) {
                    $result[$field]['fieldOptions'] = $this->_getSelectOptions($csvField->getLimit());
                }
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
     * Method that adds lookup fields with the id value to the Where clause in ORM Query
     *
     * @param  \Cake\ORM\Query $query Query instance
     * @param  string          $id    Record id
     * @return \Cake\ORM\Query
     */
    public function findByLookupFields(Query $query, $id)
    {
        $lookupFields = $this->lookupFields();

        if (empty($lookupFields)) {
            return $query;
        }

        // check for record by table's lookup fields
        foreach ($lookupFields as $lookupField) {
            $query->orWhere([$lookupField => $id]);
        }

        return $query;
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

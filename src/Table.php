<?php
namespace CsvMigrations;

use Cake\ORM\Table as BaseTable;

/**
 * Accounts Model
 *
 */
class Table extends BaseTable
{
    use CsvMigrationsTableTrait;

    /**
     * Searchable parameter name
     */
    const PARAM_NAME = 'non-searchable';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->_setAssociationsFromCsv($config);
        $this->getSearchableFields();
    }

    /**
     * Get searchable fields
     *
     * @return array field names
     */
    public function getSearchableFields()
    {
        $result = [];
        foreach ($this->getFieldsDefinitions() as $field) {
            if (!$field[static::PARAM_NAME]) {
                $result[] = $field['name'];
            }
        }

        return $result;
    }
}

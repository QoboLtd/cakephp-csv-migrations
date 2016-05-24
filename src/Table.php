<?php
namespace CsvMigrations;

use Cake\ORM\Table as BaseTable;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\MigrationTrait;

/**
 * Accounts Model
 *
 */
class Table extends BaseTable
{
    use ConfigurationTrait;
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

        /*
        set table/module configuration
         */
        $this->_setConfiguration();

        /*
        display field from configuration file
         */
        if (isset($this->_config['table']['display_field'])) {
            $this->displayField($this->_config['table']['display_field']);
        }

        /*
        set module alias from configuration file
         */
        if (isset($this->_config['table']['alias'])) {
            $this->moduleAlias($this->_config['table']['alias']);
        }

        /*
        set searchable flag from configuration file
         */
        if (isset($this->_config['table']['searchable'])) {
            $this->isSearchable($this->_config['table']['searchable']);
        }

        $this->hasMany('UploadDocuments', [
            'className' => 'Burzum/FileStorage.FileStorage',
            'foreignKey' => 'foreign_key',
        ]);
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
        foreach ($this->getFieldsDefinitions() as $field) {
            if (!$field[static::PARAM_NON_SEARCHABLE]) {
                $result[] = $field['name'];
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
}

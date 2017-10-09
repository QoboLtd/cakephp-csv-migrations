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

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DbField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Exception;
use Migrations\AbstractMigration;
use Migrations\Table;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * CSV Migration class
 */
class CsvMigration extends AbstractMigration
{
    use ConfigurationTrait;
    use MigrationTrait;

    /**
     * Migrations table object
     *
     * @var \Migrations\Table
     */
    protected $_table;

    /**
     * Field handler factory instance
     *
     * @var object
     */
    protected $_fhf;

    public $autoId = false;

    /**
     * Required table fields
     *
     * @var array
     */
    protected $_requiredFields = [
        'id' => [
            'name' => 'id',
            'type' => 'uuid',
            'required' => true,
            'non-searchable' => false,
            'unique' => true
        ],
        'created' => [
            'name' => 'created',
            'type' => 'datetime',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ],
        'modified' => [
            'name' => 'modified',
            'type' => 'datetime',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ],
        'created_by' => [
            'name' => 'created_by',
            'type' => 'related(Users)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ],
        'modified_by' => [
            'name' => 'modified_by',
            'type' => 'related(Users)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ],
        'trashed' => [
            'name' => 'trashed',
            'type' => 'datetime',
            'required' => false,
            'non-searchable' => true,
            'unique' => false
        ]
    ];

    /**
     * Method that handles migrations using csv file.
     *
     * @param  \Migrations\Table $table Migrations table object
     * @param  string            $path  csv file path
     * @return \Migrations\Table
     */
    public function csv(Table $table, $path = '')
    {
        $this->_fhf = new FieldHandlerFactory();
        $this->_table = $table;
        $this->_handleCsv();

        return $this->_table;
    }

    /**
     * Apply changes from the CSV file
     *
     * @return void
     */
    protected function _handleCsv()
    {
        $tableName = Inflector::pluralize(Inflector::classify($this->_table->getName()));
        $mc = new ModuleConfig(ConfigType::MIGRATION(), $tableName);
        $csvData = (array)json_decode(json_encode($mc->parse()), true);
        $csvData = array_merge($csvData, $this->_requiredFields);

        $tableFields = $this->_getTableFields();

        if (empty($tableFields)) {
            $this->_createFromCsv($csvData, $tableName);
        } else {
            $this->_updateFromCsv($csvData, $tableName, $tableFields);
        }
    }

    /**
     * Method that creates joined tables for Many to Many relationships.
     *
     * @param  string $tableName current table name
     * @return array             phinx table instances
     */
    public function joins($tableName)
    {
        $result = [];
        $this->setConfig($tableName);

        $manyToMany = (array)$this->getConfig(ConfigurationTrait::$CONFIG_OPTION_MANY_TO_MANY_MODULES);
        if (empty($manyToMany)) {
            return $result;
        }

        foreach ($manyToMany as $module) {
            // skip manyToMany table creation if one of the tables does not exist
            if (!$this->hasTable(Inflector::tableize($module))) {
                continue;
            }

            $tables = [];
            $tables[] = $tableName;
            /*
            associated table name
             */
            $moduleTable = TableRegistry::get($module)->table();
            $tables[] = $moduleTable;

            /*
            sort them alphabetically for CakePHP naming convention
             */
            sort($tables);

            $joinedTable = implode('_', $tables);

            /*
            skip if join table exists
             */
            if ($this->hasTable($joinedTable)) {
                continue;
            }

            /*
            construct instance of the new table
             */
            $table = $this->table($joinedTable);
            $table->addColumn('id', 'uuid', [
                'null' => false
            ]);
            $table->addColumn(Inflector::singularize($tableName) . '_id', 'uuid', [
                'null' => false
            ]);
            $table->addColumn(Inflector::singularize($moduleTable) . '_id', 'uuid', [
                'null' => false
            ]);
            $table->addColumn('created', 'datetime');
            $table->addColumn('modified', 'datetime');
            $table->addPrimaryKey(['id']);

            $result[] = $table;
        }

        return $result;
    }

    /**
     * Get table alias
     *
     * @return string
     */
    public function alias()
    {
        return Inflector::pluralize(Inflector::classify($this->_table->getName()));
    }

    /**
     * Get existing table fields.
     *
     * @return array table fields objects
     */
    protected function _getTableFields()
    {
        $result = [];
        try {
            $result = $this->_table->getColumns($this->_table->getName());
        } catch (Exception $e) {
            //
        }

        return $result;
    }

    /**
     * Create new fields from csv data
     *
     * @param  array $csvData CSV data
     * @param  string $table  Table name
     * @return void
     */
    protected function _createFromCsv(array $csvData, $table)
    {
        foreach ($csvData as $col) {
            $csvField = new CsvField($col);
            $dbFields = $this->_fhf->fieldToDb($csvField, $table);

            if (empty($dbFields)) {
                continue;
            }

            foreach ($dbFields as $dbField) {
                $this->_createColumn($dbField);
            }
        }
    }

    /**
     * Update (modify/delete) table fields in comparison to the CSV data
     *
     * @param  array  $csvData     CSV data
     * @param  string $table       Table name
     * @param  array  $tableFields Existing table fields
     * @return void
     */
    protected function _updateFromCsv(array $csvData, $table, array $tableFields)
    {
        // get existing table column names
        foreach ($tableFields as &$tableField) {
            $tableField = $tableField->getName();
        }

        // keep track of edited columns
        $editedColumns = [];
        foreach ($csvData as $col) {
            $csvField = new CsvField($col);
            $dbFields = $this->_fhf->fieldToDb($csvField, $table);

            if (empty($dbFields)) {
                continue;
            }

            foreach ($dbFields as $dbField) {
                // edit existing column
                if (in_array($dbField->getName(), $tableFields)) {
                    $editedColumns[] = $dbField->getName();
                    $this->_updateColumn($dbField);
                } else { // add new column
                    $this->_createColumn($dbField);
                }
            }
        }

        // remove unneeded columns
        foreach (array_diff($tableFields, $editedColumns) as $fieldName) {
            $this->_deleteColumn($fieldName);
        }
    }

    /**
     * Method used for creating new DB table column.
     *
     * @param  \CsvMigrations\FieldHandlers\DbField $dbField DbField object
     * @return void
     */
    protected function _createColumn(DbField $dbField)
    {
        $this->_table->addColumn($dbField->getName(), $dbField->getType(), $dbField->getOptions());

        // set id as primary key
        if ('id' === $dbField->getName()) {
            $this->_table->addPrimaryKey([
                $dbField->getName(),
            ]);
        }

        $this->_addIndexes($dbField, false);
    }

    /**
     * Method used for updating an existing DB table column.
     *
     * @param  \CsvMigrations\FieldHandlers\DbField $dbField DbField object
     * @return void
     */
    protected function _updateColumn(DbField $dbField)
    {
        $this->_table->changeColumn($dbField->getName(), $dbField->getType(), $dbField->getOptions());
        // set field as unique
        if ($dbField->getUnique()) {
            // avoid creation of duplicate indexes
            if (!$this->_table->hasIndex($dbField->getName())) {
                $this->_table->addIndex([$dbField->getName()], ['unique' => true]);
            }
        } else {
            if ($this->_table->hasIndex($dbField->getName())) {
                $this->_table->removeIndexByName($dbField->getName());
            }
        }

        $this->_addIndexes($dbField);
    }

    /**
     * Adds indexes to specified dbField.
     *
     * @param \CsvMigrations\FieldHandlers\DbField $dbField DbField object
     * @param bool $exists Table exists flag
     * @return void
     */
    protected function _addIndexes(DbField $dbField, $exists = true)
    {
        if ('id' === $dbField->getName()) {
            return;
        }

        $this->_removeIndexes($dbField, $exists);

        $added = false;
        if ($dbField->getUnique()) {
            $added = $this->_addIndex($dbField, 'unique', $exists);
        }

        if (!$added && 'uuid' === $dbField->getType()) {
            $this->_addIndex($dbField, 'lookup', $exists);
        }
    }

    /**
     * Remove column indexes.
     *
     * @param \CsvMigrations\FieldHandlers\DbField $dbField DbField object
     * @param bool $exists Table exists flag
     * @return void
     */
    protected function _removeIndexes(DbField $dbField, $exists = true)
    {
        if (!(bool)$exists) {
            return;
        }

        // remove legacy index
        $this->_table->removeIndexByName($dbField->getName());

        // remove unique index
        if (!$dbField->getUnique() && $this->_table->hasIndex($dbField->getName())) {
            $indexName = 'unique_' . $dbField->getName();
            $this->_table->removeIndexByName($indexName);
        }

        // remove lookup index
        if ('uuid' !== $dbField->getType() && $this->_table->hasIndex($dbField->getName())) {
            $indexName = 'lookup_' . $dbField->getName();
            $this->_table->removeIndexByName($indexName);
        }
    }

    /**
     * Add column index by type.
     *
     * @param \CsvMigrations\FieldHandlers\DbField $dbField DbField object
     * @param string $type Index type
     * @param bool $exists Table exists flag
     * @return bool
     */
    protected function _addIndex(DbField $dbField, $type, $exists = true)
    {
        if (empty($type) || !is_string($type)) {
            return false;
        }

        // skip if table exists and has specified index
        if ($exists && $this->_table->hasIndex($dbField->getName())) {
            return false;
        }

        $options = [];
        $options['name'] = $type . '_' . $dbField->getName();
        if ('unique' === $type) {
            $options['unique'] = true;
        }

        $this->_table->addIndex($dbField->getName(), $options);

        return true;
    }

    /**
     * Method used for deleting an existing DB table column.
     *
     * @param  string $fieldName Table column name
     * @return void
     */
    protected function _deleteColumn($fieldName)
    {
        $this->_table->removeColumn($fieldName);
    }
}

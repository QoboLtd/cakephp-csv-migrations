<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DbField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\Parser\Csv\MigrationParser;
use Migrations\AbstractMigration;
use Migrations\Table;
use RuntimeException;

/**
 * CSV Migration class
 */
class CsvMigration extends AbstractMigration
{
    use ConfigurationTrait;
    use MigrationTrait;

    /**
     * File extension
     */
    const EXTENSION = 'csv';

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
        'trashed' => [
            'name' => 'trashed',
            'type' => 'datetime',
            'required' => true,
            'non-searchable' => true,
            'unique' => false
        ]
    ];

    /**
     * Method that handles migrations using csv file.
     *
     * @param  \Migrations\Table $table Migrations table object
     * @param  string            $path  csv file path
     * @throws \RuntimeException
     * @return \Migrations\Table
     */
    public function csv(Table $table, $path = '')
    {
        $this->_fhf = new FieldHandlerFactory();
        $this->_table = $table;
        $this->_handleCsv($path);

        return $this->_table;
    }

    /**
     * Apply changes from the CSV file
     *
     * @param string $path Path to the CSV file
     * @return void
     */
    protected function _handleCsv($path = '')
    {
        $tableName = Inflector::pluralize(Inflector::classify($this->_table->getName()));
        if ('' === trim($path)) {
            $path = Configure::readOrFail('CsvMigrations.migrations.path') . $tableName . DS;
            $path .= Configure::readOrFail('CsvMigrations.migrations.filename') . '.' . static::EXTENSION;
        }

        $parser = new MigrationParser();
        $csvData = $parser->wrapFromPath($path);
        $csvData = array_merge($csvData, $this->_requiredFields);

        $tableFields = $this->_getTableFields();

        if (empty($tableFields)) {
            $this->_createFromCsv($csvData);
        } else {
            $this->_updateFromCsv($csvData, $tableFields);
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
        $this->_setConfiguration($tableName);

        if (empty($this->_config['manyToMany']['modules'])) {
            return $result;
        }

        $manyToMany = explode(',', $this->_config['manyToMany']['modules']);

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
        } catch (\Exception $e) {
            //
        }

        return $result;
    }

    /**
     * Create new fields from csv data.
     *
     * @param  array $csvData csv data
     * @return void
     */
    protected function _createFromCsv(array $csvData)
    {
        foreach ($csvData as $col) {
            $csvField = new CsvField($col);
            $dbFields = $this->_fhf->fieldToDb($csvField);

            if (empty($dbFields)) {
                continue;
            }

            foreach ($dbFields as $dbField) {
                $this->_createColumn($dbField);
            }
        }
    }

    /**
     * Update (modify/delete) table fields in comparison to the csv data.
     *
     * @param  array $csvData      csv data
     * @param  array $tableFields  existing table fields
     * @return void
     */
    protected function _updateFromCsv(array $csvData, array $tableFields)
    {
        // get existing table column names
        foreach ($tableFields as &$tableField) {
            $tableField = $tableField->getName();
        }

        // keep track of edited columns
        $editedColumns = [];
        foreach ($csvData as $col) {
            $csvField = new CsvField($col);
            $dbFields = $this->_fhf->fieldToDb($csvField);

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
        $this->_table->addColumn($dbField->getName(), $dbField->getType(), [
            'limit' => $dbField->getLimit(),
            'null' => $dbField->getRequired() ? false : true
        ]);
        // set field as unique
        if ($dbField->getUnique()) {
            $this->_table->addIndex([$dbField->getName()], ['unique' => $dbField->getUnique()]);
        }
        // set id as primary key
        if ('id' === $dbField->getName()) {
            $this->_table->addPrimaryKey([
                $dbField->getName(),
            ]);
        }
    }

    /**
     * Method used for updating an existing DB table column.
     *
     * @param  \CsvMigrations\FieldHandlers\DbField $dbField DbField object
     * @return void
     */
    protected function _updateColumn(DbField $dbField)
    {
        $this->_table->changeColumn($dbField->getName(), $dbField->getType(), [
            'limit' => $dbField->getLimit(),
            'null' => (bool)$dbField->getRequired() ? false : true
        ]);
        // set field as unique
        if ($dbField->getUnique()) {
            $this->_table->addIndex([$dbField->getName()], ['unique' => $dbField->getUnique()]);
        }
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

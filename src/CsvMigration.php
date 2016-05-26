<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
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
     * @var \Migrations\Table
     */
    protected $_table;

    /**
     * Field handler factory instance
     * @var object
     */
    protected $_fhf;

    public $autoId = false;

    /**
     * Method that handles migrations using csv file.
     * @param  \Migrations\Table $table Migrations table object
     * @param  string            $path  csv file path
     * @throws \RuntimeException
     * @return \Migrations\Table
     */
    public function csv(\Migrations\Table $table, $path = '')
    {
        $this->_fhf = new FieldHandlerFactory();
        $this->_table = $table;
        $this->_handleCsv($path);

        return $this->_table;
    }

    protected function _handleCsv($path = '')
    {
        $tableName = Inflector::pluralize(Inflector::classify($this->_table->getName()));
        if ('' === trim($path)) {
            $path = Configure::readOrFail('CsvMigrations.migrations.path') . $tableName . DS;
            $path .= Configure::readOrFail('CsvMigrations.migrations.filename') . '.' . static::EXTENSION;
        }

        $csvData = $this->_getCsvData($path);

        if (empty($csvData)) {
            throw new RuntimeException('No CSV data found for [' . $tableName . '] module.');
        }

        $csvData = $this->_prepareCsvData($csvData);
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
        $this->_setConfiguration();

        if (empty($this->_config['manyToMany']['modules'])) {
            return $result;
        }

        $manyToMany = explode(',', $this->_config['manyToMany']['modules']);

        foreach ($manyToMany as $module) {
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

    public function alias()
    {
        return Inflector::pluralize(Inflector::classify($this->_table->getName()));
    }

    /**
     * Get existing table fields.
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
                $this->_table->addColumn($dbField->getName(), $dbField->getType(), [
                    'limit' => $dbField->getLimit(),
                    'null' => $dbField->getRequired() ? false : true
                ]);
                // set id as primary key
                if ('id' === $dbField->getName()) {
                    $this->_table->addPrimaryKey([
                        $dbField->getName(),
                    ]);
                }
            }
        }
    }

    /**
     * Update (modify/delete) table fields in comparison to the csv data.
     * @param  array $csvData      csv data
     * @param  array $tableFields  existing table fields
     * @return void
     */
    protected function _updateFromCsv(array $csvData, array $tableFields)
    {
        // store all table field names
        $tableFieldNames = [];
        foreach ($tableFields as $tableField) {
            $tableFieldName = $tableField->getName();
            $tableFieldNames[] = $tableFieldName;

            // remove missing fields
            if (!in_array($tableFieldName, array_keys($csvData))) {
                $this->_table->removeColumn($tableFieldName);
            } else {
                $csvField = new CsvField($csvData[$tableFieldName]);
                $dbFields = $this->_fhf->fieldToDb($csvField);

                if (empty($dbFields)) {
                    continue;
                }

                foreach ($dbFields as $dbField) {
                    $this->_table->changeColumn($dbField->getName(), $dbField->getType(), [
                        'limit' => $dbField->getLimit(),
                        'null' => (bool)$dbField->getRequired() ? false : true
                    ]);
                }
            }
        }

        // add new fields
        $newFields = [];
        foreach (array_keys($csvData) as $csvField) {
            if (!in_array($csvData[$csvField]['name'], $tableFieldNames)) {
                $newFields[] = $csvData[$csvField];
            }
        }
        $this->_createFromCsv($newFields);
    }
}

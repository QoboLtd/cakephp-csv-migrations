<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use Migrations\AbstractMigration;

/**
 * CSV Migration class
 */
class CsvMigration extends AbstractMigration
{
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
     * Field parameters
     * @var array
     */
    protected $_fieldParams = ['name', 'type', 'limit', 'required'];

    /**
     * Supported field types
     * @var array
     */
    protected $_supportedTypes = ['uuid', 'string', 'integer', 'boolean', 'text', 'datetime', 'date', 'time'];

    /**
     * Error messages
     * @var array
     */
    protected $_errorMessages = [
        '_createFromCsv' => 'Field parameters count [%s] does not match required parameters count [%s]',
        '_validateField' => 'Field type [%s] not supported'
    ];

    public $autoId = false;

    /**
     * Method that handles migrations using csv file.
     * @param  \Migrations\Table $table Migrations table object
     * @param  string            $path  csv file path
     * @return \Migrations\Table
     */
    public function csv(\Migrations\Table $table, $path = '')
    {
        if ('' === trim($path)) {
            $path = Configure::readOrFail('CsvMigrations.migrations.path');
            $path .= Inflector::pluralize(Inflector::classify($table->getName())) . DS;
            $path .= Configure::readOrFail('CsvMigrations.migrations.filename') . '.' . static::EXTENSION;
        }
        $this->_table = $table;
        $csvData = $this->_getCsvData($path);
        $tableFields = $this->_getTableFields();

        if (empty($tableFields)) {
            $this->_createFromCsv($csvData);
        } else {
            $this->_updateFromCsv($csvData, $tableFields);
        }

        return $this->_table;
    }

    /**
     * Method that retrieves csv file data.
     * @param  string $path csv file path
     * @return array        csv data
     * @todo this method should be moved to a Trait class as is used throught Csv Migrations and Csv Views plugins
     */
    protected function _getCsvData($path)
    {
        $result = [];
        if (file_exists($path)) {
            if (false !== ($handle = fopen($path, 'r'))) {
                $row = 0;
                while (false !== ($data = fgetcsv($handle, 0, ','))) {
                    // skip first row
                    if (0 === $row) {
                        $row++;
                        continue;
                    }
                    $result[] = $data;
                }
                fclose($handle);
            }
        }

        return $result;
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
     * @param  array $csvData csv data
     * @throws \RuntimeException when csv field parameters count does not match
     * @return void
     */
    protected function _createFromCsv(array $csvData)
    {
        $paramsCount = count($this->_fieldParams);
        foreach ($csvData as $col) {
            $colCount = count($col);
            if ($colCount !== $paramsCount) {
                throw new \RuntimeException(sprintf($this->_errorMessages[__FUNCTION__], $colCount, $paramsCount));
            }
            $field = array_combine($this->_fieldParams, $col);
            // set to string if list field
            if ($this->_isListField($field['type'])) {
                $field['type'] = 'string';
            }
            // set to uuid if foreign key
            if ($this->_isForeignKey($field['type'])) {
                $field['type'] = 'uuid';
            }
            if ($this->_validateField($field)) {
                $this->_table->addColumn($field['name'], $field['type'], [
                    'limit' => $field['limit'],
                    'null' => (bool)$field['required'] ? false : true
                ]);
                // set id as primary key
                if ('id' === $field['name']) {
                    $this->_table->addPrimaryKey([
                        $field['name'],
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
        $csvData = $this->_prepareCsvData($csvData);

        // store all table field names
        $tableFieldNames = [];
        foreach ($tableFields as $tableField) {
            $tableFieldName = $tableField->getName();
            $tableFieldNames[] = $tableFieldName;

            // remove missing fields
            if (!in_array($tableFieldName, array_keys($csvData))) {
                $this->_table->removeColumn($tableFieldName);
            } else {
                // store table field parameters in an array
                $tableField = array_combine($this->_fieldParams, [
                    $tableFieldName,
                    $tableField->getType(),
                    $tableField->getLimit(),
                    $tableField->getNull()
                ]);

                // if table field and csv field parameters do not match, modify the table field
                if (!empty(array_diff(array_values($tableField), $csvData[$tableField['name']]))) {
                    $result = array_combine($this->_fieldParams, $csvData[$tableField['name']]);
                    // set to string if list field
                    if ($this->_isListField($result['type'])) {
                        $result['type'] = 'string';
                    }
                    // set to uuid if foreign key
                    if ($this->_isForeignKey($result['type'])) {
                        $result['type'] = 'uuid';
                    }
                    $this->_table->changeColumn($result['name'], $result['type'], [
                        'limit' => $result['limit'],
                        'null' => (bool)$result['required'] ? false : true
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

    /**
     * Method that determines, from its type, if field is a list type.
     * @param  string  $type field type
     * @return boolean       true if is list field, false otherwise
     */
    protected function _isListField($type)
    {
        return false !== strpos($type, 'list:');
    }

    /**
     * Method that determines, from its type, if field is a foreign key.
     * @param  string $type field type
     * @return boolean      true if is foreign key, false otherwise
     */
    protected function _isForeignKey($type)
    {
        return false !== strpos($type, 'related:');
    }

    /**
     * Validate field.
     * @param  array $field field info
     * @throws \RuntimeException when field type is not supported
     * @return bool
     */
    protected function _validateField(array $field)
    {
        if (!in_array($field['type'], $this->_supportedTypes)) {
            throw new \RuntimeException(sprintf($this->_errorMessages[__FUNCTION__], $field['type']));
        }

        return true;
    }

    /**
     * Method that restructures csv data for better handling and searching through.
     * @param  array  $csvData csv data
     * @return array
     */
    protected function _prepareCsvData(array $csvData)
    {
        $result = [];
        foreach ($csvData as $v) {
            $result[$v[0]] = array_combine($this->_fieldParams, $v);
        }

        return $result;
    }
}

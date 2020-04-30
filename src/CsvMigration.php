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

use Cake\Log\LogTrait;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DbField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Migrations\AbstractMigration;
use Migrations\Table;
use PDOException;
use Psr\Log\LogLevel;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Webmozart\Assert\Assert;

/**
 * This class is responsible for handling all CSV migrations.
 */
class CsvMigration extends AbstractMigration
{
    use LogTrait;

    /**
     * Migrations table object
     *
     * @var \Migrations\Table
     */
    private $table;

    /**
     * Field handler factory instance.
     *
     * @var \CsvMigrations\FieldHandlers\FieldHandlerFactory
     */
    private $factory;

    public $autoId = false;

    /**
     * Required table fields
     *
     * @var mixed[]
     */
    protected static $_requiredFields = [
        'id' => [
            'name' => 'id',
            'type' => 'uuid',
            'required' => true,
            'non-searchable' => false,
            'unique' => true,
        ],
        'created' => [
            'name' => 'created',
            'type' => 'datetime',
            'required' => false,
            'non-searchable' => false,
            'unique' => false,
        ],
        'modified' => [
            'name' => 'modified',
            'type' => 'datetime',
            'required' => false,
            'non-searchable' => false,
            'unique' => false,
        ],
        'created_by' => [
            'name' => 'created_by',
            'type' => 'related(Users)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false,
        ],
        'modified_by' => [
            'name' => 'modified_by',
            'type' => 'related(Users)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false,
        ],
        'trashed' => [
            'name' => 'trashed',
            'type' => 'datetime',
            'required' => false,
            'non-searchable' => true,
            'unique' => false,
        ],
    ];

    /**
     * Method that handles migrations using JSON file.
     *
     * @param \Migrations\Table $table Migrations table object
     * @param string $path JSON File path
     * @return \Migrations\Table
     */
    public function csv(Table $table, string $path = ''): Table
    {
        $this->factory = new FieldHandlerFactory();
        $this->table = $table;
        $this->handleCsv();

        return $this->table;
    }

    /**
     * Apply changes from the JSON file
     *
     * @return void
     */
    private function handleCsv(): void
    {
        $tableName = $this->table->getName();
        Assert::notNull($tableName);

        $tableName = Inflector::pluralize(Inflector::classify($tableName));
        $mc = new ModuleConfig(ConfigType::MIGRATION(), $tableName);
        $data = $mc->parseToArray();
        if (empty($data)) {
            $this->log(sprintf('No data found for %s module', $tableName), LogLevel::ERROR);

            return;
        }

        $data = array_merge($data, self::$_requiredFields);
        try {
            $tableFields = $this->table->getColumns();
        } catch (PDOException $e) {
            $tableFields = [];
        }

        empty($tableFields) ?
            $this->createFromCsv($data, $tableName) :
            $this->updateFromCsv($data, $tableName, $tableFields);
    }

    /**
     * Required fields getter method.
     *
     * Returns either just the field names or with their schema definition.
     *
     * @param bool $withSchema Schema inclusion flag
     * @return mixed[]|string[]
     */
    public static function getRequiredFields(bool $withSchema = false): array
    {
        return $withSchema ? self::$_requiredFields : array_keys(self::$_requiredFields);
    }

    /**
     * Create new fields from JSON data.
     *
     * @param mixed[] $data JSON data
     * @param string $table Table name
     * @return void
     */
    private function createFromCsv(array $data, string $table): void
    {
        foreach ($data as $col) {
            $csvField = new CsvField($col);
            $dbFields = $this->factory->fieldToDb($csvField, $table);

            if (empty($dbFields)) {
                continue;
            }

            foreach ($dbFields as $dbField) {
                $this->createColumn($dbField);
            }
        }
    }

    /**
     * Update (modify/delete) table fields in comparison to the JSON data.
     *
     * @param mixed[] $data JSON data
     * @param string $table Table name
     * @param \Phinx\Db\Table\Column[] $fields Existing table fields
     * @return void
     */
    private function updateFromCsv(array $data, string $table, array $fields): void
    {
        $tableFields = [];
        // get existing table column names
        foreach ($fields as $field) {
            $tableFields[] = $field->getName();
        }

        // keep track of edited columns
        $editedColumns = [];
        foreach ($data as $col) {
            $csvField = new CsvField($col);
            $dbFields = $this->factory->fieldToDb($csvField, $table);

            if (empty($dbFields)) {
                continue;
            }

            foreach ($dbFields as $dbField) {
                // edit existing column
                if (in_array($dbField->getName(), $tableFields)) {
                    $editedColumns[] = $dbField->getName();
                    $this->updateColumn($dbField);
                } else { // add new column
                    $this->createColumn($dbField);
                }
            }
        }

        // remove unneeded columns
        foreach (array_diff($tableFields, $editedColumns) as $fieldName) {
            Assert::notNull($fieldName);
            $this->deleteColumn($fieldName);
        }
    }

    /**
     * Method used for creating new DB table column.
     *
     * @param \CsvMigrations\FieldHandlers\DbField $dbField DbField object
     * @return void
     */
    private function createColumn(DbField $dbField): void
    {
        $this->table->addColumn($dbField->getName(), $dbField->getType(), $dbField->getOptions());

        // set id as primary key
        if ('id' === $dbField->getName()) {
            $this->table->addPrimaryKey([
                $dbField->getName(),
            ]);
        }

        $this->addIndexes($dbField, false);
    }

    /**
     * Method used for updating an existing DB table column.
     *
     * @param \CsvMigrations\FieldHandlers\DbField $dbField DbField object
     * @return void
     */
    private function updateColumn(DbField $dbField): void
    {
        $this->table->changeColumn($dbField->getName(), $dbField->getType(), $dbField->getOptions());
        // set field as unique
        if ($dbField->getUnique()) {
            // avoid creation of duplicate indexes
            if (!$this->table->hasIndex($dbField->getName())) {
                $this->table->addIndex([$dbField->getName()], ['unique' => true]);
            }
        } else {
            if ($this->table->hasIndex($dbField->getName())) {
                $this->table->removeIndexByName($dbField->getName());
            }
        }

        $this->addIndexes($dbField);
    }

    /**
     * Adds indexes to specified dbField.
     *
     * @param \CsvMigrations\FieldHandlers\DbField $dbField DbField object
     * @param bool $exists Table exists flag
     * @return void
     */
    private function addIndexes(DbField $dbField, bool $exists = true): void
    {
        if ('id' === $dbField->getName()) {
            return;
        }

        $this->removeIndexes($dbField, $exists);

        $added = false;
        if ($dbField->getUnique()) {
            $added = $this->addIndex($dbField, 'unique', $exists);
        }

        if (!$added && 'uuid' === $dbField->getType()) {
            $this->addIndex($dbField, 'lookup', $exists);
        }
    }

    /**
     * Remove column indexes.
     *
     * @param \CsvMigrations\FieldHandlers\DbField $dbField DbField object
     * @param bool $exists Table exists flag
     * @return void
     */
    private function removeIndexes(DbField $dbField, bool $exists = true): void
    {
        if (! $exists) {
            return;
        }

        // remove legacy index
        $this->table->removeIndexByName($dbField->getName());

        // remove unique index
        if (!$dbField->getUnique() && $this->table->hasIndex($dbField->getName())) {
            $indexName = 'unique_' . $dbField->getName();
            $this->table->removeIndexByName($indexName);
        }

        // remove lookup index
        if ('uuid' !== $dbField->getType() && $this->table->hasIndex($dbField->getName())) {
            $indexName = 'lookup_' . $dbField->getName();
            $this->table->removeIndexByName($indexName);
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
    private function addIndex(DbField $dbField, string $type, bool $exists = true): bool
    {
        if (empty($type)) {
            return false;
        }

        // skip if table exists and has specified index
        if ($exists && $this->table->hasIndex($dbField->getName())) {
            return false;
        }

        $options = [];
        $options['name'] = $type . '_' . $dbField->getName();
        if ('unique' === $type) {
            $options['unique'] = true;
        }

        $this->table->addIndex($dbField->getName(), $options);

        return true;
    }

    /**
     * Method used for deleting an existing DB table column.
     *
     * @param string $fieldName Table column name
     * @return void
     */
    private function deleteColumn(string $fieldName): void
    {
        $this->table->removeColumn($fieldName);
    }
}

<?php
namespace CsvMigrations\Aggregator;

use Cake\ORM\TableRegistry;
use InvalidArgumentException;

/**
 * This class is used as a storage object for aggregator configuration.
 */
final class Configuration
{
    /**
     * Aggregation table.
     *
     * @var Cake\Datasource\RepositoryInterface
     */
    private $table;

    /**
     * Aggregation field.
     *
     * @var string
     */
    private $field;

    /**
     * Join table instance. Optional as it is used only in limited mode.
     *
     * @var Cake\Datasource\RepositoryInterface
     */
    private $joinTable = null;

    /**
     * Field used for displaying purposes,
     *
     * @var string
     */
    private $displayField;

    /**
     * Constructor method.
     *
     * Mostly used for properties assignment.
     *
     * @param string $tableName Aggregation table name
     * @param string $field Aggregate field name
     * @param string $displayField Display field name
     * @param string $joinTable Join Table name
     * @return void
     */
    public function __construct($tableName, $field, $displayField = '', $joinTable = '')
    {
        // basic string validation, this can be removed on PHP 7 with string typehinting.
        foreach (func_get_args() as $key => $argument) {
            if (! is_string($argument)) {
                throw new InvalidArgumentException(sprintf(
                    'Argument %d passed to %s() must be of the type string, %s given.',
                    $key + 1,
                    __METHOD__,
                    gettype($argument)
                ));
            }
        }

        $this->table = TableRegistry::get($tableName);
        if ('' !== trim($joinTable)) {
            $this->joinTable = TableRegistry::get($joinTable);
        }

        $this->field = $field;
        $this->displayField = $displayField ? $displayField : $field;
    }

    /**
     * Aggregate table getter.
     *
     * @return \Cake\Datasource\RepositoryInterface
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Aggregate field getter.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Join table getter.
     *
     * @return \Cake\Datasource\RepositoryInterface
     */
    public function getJoinTable()
    {
        return $this->joinTable;
    }

    /**
     * Display field getter.
     *
     * @return string
     */
    public function getDisplayField()
    {
        return $this->displayField;
    }
}

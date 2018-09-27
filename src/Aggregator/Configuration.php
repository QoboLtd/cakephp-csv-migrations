<?php
namespace CsvMigrations\Aggregator;

use Cake\Datasource\EntityInterface;
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
     * Entity instance. This is only required if aggregation is in limited mode.
     *
     * @var \Cake\Datasource\EntityInterface|null
     */
    private $entity = null;

    /**
     * Constructor method.
     *
     * Mostly used for properties assignment and data validation.
     *
     * @param string $tableName Aggregation table name
     * @param string $field Aggregate field name
     * @param string $displayField Display field name, optional
     * @param string $joinTable Join Table name, optional
     * @param \Cake\Datasource\EntityInterface|null $entity Entity instance from Join table
     * @return void
     */
    public function __construct($tableName, $field, $displayField = '', $joinTable = '', EntityInterface $entity = null)
    {
        // basic string validation, this can be removed on PHP 7 with string typehinting.
        foreach ([$tableName, $field, $displayField, $joinTable] as $key => $argument) {
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
        $this->entity = $entity;

        if ($this->joinMode() && null === $this->entity) {
            throw new InvalidArgumentException('Running aggregation in limited mode, entity argument is required.');
        }

        if ($this->joinMode() && get_class($this->entity) !== $this->joinTable->getEntityClass()) {
            throw new InvalidArgumentException(sprintf(
                'Entity must be an instance of "%s". Instead, instance of "%s" was provided.',
                $this->joinTable->getEntityClass(),
                get_class($this->entity)
            ));
        }
    }

    /**
     * Running on join mode if join table is defined.
     *
     * @return bool
     */
    public function joinMode()
    {
        return null !== $this->joinTable;
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

    /**
     * Entity getter.
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }
}

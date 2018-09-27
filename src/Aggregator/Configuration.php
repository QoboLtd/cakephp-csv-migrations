<?php
namespace CsvMigrations\Aggregator;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
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
    private $displayField = '';

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
     * @param \Cake\Datasource\RepositoryInterface $table Aggregate table instance
     * @param string $field Aggregate field name
     * @return void
     */
    public function __construct(RepositoryInterface $table, $field)
    {
        // string validation, this can be removed on PHP 7 with string typehinting.
        if (! is_string($field)) {
            throw new InvalidArgumentException(sprintf(
                'Argument 2 passed to %s() must be of the type string, %s given.',
                __METHOD__,
                gettype($field)
            ));
        }

        $this->table = $table;
        $this->field = $field;
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
     * Display field getter.
     *
     * @return string
     */
    public function getDisplayField()
    {
        if ('' === trim($this->displayField)) {
            return $this->field;
        }

        return $this->displayField;
    }

    /**
     * Display field setter.
     *
     * @param string $displayField Display field name
     * @return \CsvMigrations\Aggregator\Configuration
     */
    public function setDisplayField($displayField)
    {
        // string validation, this can be removed on PHP 7 with string typehinting.
        if (! is_string($displayField)) {
            throw new InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be of the type string, %s given.',
                __METHOD__,
                gettype($displayField)
            ));
        }

        $this->displayField = $displayField;

        return $this;
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
     * Entity getter.
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Join data setter.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Join table intsance
     * @param \Cake\Datasource\EntityInterface $entity Entity instance from join table
     * @return \CsvMigrations\Aggregator\Configuration
     */
    public function setJoinData(RepositoryInterface $table, EntityInterface $entity)
    {
        $entityClass = $table->getEntityClass();
        if (! $entity instanceof $entityClass) {
            throw new InvalidArgumentException(sprintf(
                'Entity must be an instance of "%s". Instead, instance of "%s" was provided.',
                $table->getEntityClass(),
                get_class($entity)
            ));
        }

        $this->joinTable = $table;
        $this->entity = $entity;

        return $this;
    }
}

<?php
namespace CsvMigrations\Aggregator;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use CsvMigrations\MissingJoinException;
use InvalidArgumentException;

/**
 * This class is used as a storage object for aggregator configuration.
 */
final class Configuration
{
    /**
     * Aggregation table.
     *
     * @var \Cake\ORM\Table
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
     * @var \Cake\ORM\Table|null
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
     * @param \Cake\ORM\Table $table Aggregate table instance
     * @param string $field Aggregate field name
     */
    public function __construct(Table $table, string $field)
    {
        $this->table = $table;
        $this->field = $field;
    }

    /**
     * Running on join mode if join table is defined.
     *
     * @return bool
     */
    public function joinMode() : bool
    {
        return null !== $this->joinTable;
    }

    /**
     * Aggregate table getter.
     *
     * @return \Cake\ORM\Table
     */
    public function getTable() : Table
    {
        return $this->table;
    }

    /**
     * Aggregate field getter.
     *
     * @return string
     */
    public function getField() : string
    {
        return $this->field;
    }

    /**
     * Display field getter.
     *
     * @return string
     */
    public function getDisplayField() : string
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
     * @return self
     */
    public function setDisplayField(string $displayField) : self
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
     * @return \Cake\ORM\Table
     */
    public function getJoinTable() : Table
    {
        if ($this->joinTable instanceof Table) {
            return $this->joinTable;
        }

        throw new MissingJoinException('No join table has been defined');
    }

    /**
     * Entity getter.
     *
     * @return \Cake\Datasource\EntityInterface
     */
    public function getEntity() : EntityInterface
    {
        if ($this->entity instanceof EntityInterface) {
            return $this->entity;
        }

        throw new MissingJoinException('No join table has been defined');
    }

    /**
     * Join data setter.
     *
     * @param \Cake\ORM\Table $table Join table intsance
     * @param \Cake\Datasource\EntityInterface $entity Entity instance from join table
     * @return self
     */
    public function setJoinData(Table $table, EntityInterface $entity) : self
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

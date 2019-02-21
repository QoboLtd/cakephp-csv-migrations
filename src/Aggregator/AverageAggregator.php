<?php
namespace CsvMigrations\Aggregator;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;
use Cake\ORM\Query;
use Webmozart\Assert\Assert;

final class AverageAggregator extends AbstractAggregator
{
    /**
     * Aggregator supported database types.
     *
     * @var array
     */
    private $supportedTypes = ['integer', 'decimal'];

    /**
     * {@inheritDoc}
     */
    public function validate() : bool
    {
        if (! parent::validate()) {
            return false;
        }

        $table = $this->getConfig()->getTable();

        $type = $table->getSchema()
            ->getColumnType($this->getConfig()->getField());

        if (! in_array($type, $this->supportedTypes)) {
            $this->errors[] = sprintf(
                'Unsupported column type %s. Supported types are %s',
                $type,
                implode(', ', $this->supportedTypes)
            );

            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function applyConditions(QueryInterface $query) : QueryInterface
    {
        $table = $this->getConfig()->getTable();
        Assert::isInstanceOf($query, Query::class);

        $aggregateField = $table->aliasField($this->getConfig()->getField());

        $query->select(['average' => $query->func()->avg($aggregateField)]);

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(EntityInterface $entity)
    {
        return $entity->get('average');
    }
}

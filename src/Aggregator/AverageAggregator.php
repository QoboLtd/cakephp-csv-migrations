<?php
namespace CsvMigrations\Aggregator;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;

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
    public function validate()
    {
        if (! parent::validate()) {
            return false;
        }

        $type = $this->getConfig()
            ->getTable()
            ->getSchema()
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
    public function applyConditions(QueryInterface $query)
    {
        $query->select(['average' => $query->func()->avg($this->getConfig()->getField())]);

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

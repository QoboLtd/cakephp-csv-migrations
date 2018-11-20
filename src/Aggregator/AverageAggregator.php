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
    public function validate() : bool
    {
        if (! parent::validate()) {
            return false;
        }

        /** @var \Cake\Datasource\RepositoryInterface&\Cake\ORM\Table */
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
        /** @var \Cake\Datasource\QueryInterface&\Cake\Database\Query */
        $query = $query;

        /** @var \Cake\Datasource\RepositoryInterface&\Cake\ORM\Table */
        $table = $this->getConfig()->getTable();

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

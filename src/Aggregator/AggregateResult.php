<?php
namespace CsvMigrations\Aggregator;

use Cake\Datasource\QueryInterface;
use Cake\Log\Log;
use Cake\ORM\Association;
use RuntimeException;

final class AggregateResult
{
    /**
     * Aggregator execution method.
     *
     * @param \CsvMigrations\Aggregator\AggregatorInterface $aggregator Aggregator instance
     * @return mixed
     */
    public static function get(AggregatorInterface $aggregator)
    {
        $config = $aggregator->getConfig();

        /** @var \Cake\Datasource\QueryInterface&\Cake\Database\Query */
        $query = $config->getTable()->find('all');
        $query = $aggregator->applyConditions($query);
        $query = static::join($aggregator, $query);

        $entity = $query->first();
        if (null === $entity) {
            return '';
        }

        return $aggregator->getResult($entity);
    }

    /**
     * Aggregator query is joined whenever the entity instance is set, meaning
     * that the results will be limited to the entity's associated records.
     *
     * @param \CsvMigrations\Aggregator\AggregatorInterface $aggregator Aggregator instance
     * @param \Cake\Datasource\QueryInterface $query Query object
     * @return \Cake\Datasource\QueryInterface
     */
    private static function join(AggregatorInterface $aggregator, QueryInterface $query) : QueryInterface
    {
        $config = $aggregator->getConfig();

        if (! $config->joinMode()) {
            return $query;
        }

        /** @var \Cake\Datasource\QueryInterface&\Cake\ORM\Query */
        $query = $query;

        $association = static::findAssociation($aggregator);

        // limit to record's associated data
        $query->innerJoinWith($association->getName(), function ($q) use ($association, $config) {
            /** @var \Cake\Datasource\RepositoryInterface&\Cake\ORM\Table */
            $table = $config->getJoinTable();

            $primaryKey = $table->getPrimaryKey();
            if (! is_string($primaryKey)) {
                Log::error('Failed to apply inner join for aggregated field value: primary key must be a string', [
                    'source_table' => $config->getTable()->getAlias(),
                    'target_table' => $table->getAlias(),
                    'primar_key' => $table->getPrimaryKey(),
                    'association' => $association->getName()
                ]);

                return $q;
            }

            /** @var \Cake\Datasource\EntityInterface */
            $entity = $config->getEntity();

            return $q->where([
                $association->getTarget()->aliasField($primaryKey) => $entity->get($primaryKey)
            ]);
        });

        return $query;
    }

    /**
     * Association instance finder.
     *
     * This is from the target table's side, since the aggregation is applied on its column.
     *
     * @param \CsvMigrations\Aggregator\AggregatorInterface $aggregator Aggregator instance
     * @return \Cake\ORM\Association
     * @throws \RuntimeException When association is not found between source and target tables.
     */
    private static function findAssociation(AggregatorInterface $aggregator) : Association
    {
        $config = $aggregator->getConfig();
        /** @var \Cake\Datasource\RepositoryInterface&\Cake\ORM\Table */
        $table = $config->getTable();
        /** @var \Cake\Datasource\RepositoryInterface&\Cake\ORM\Table */
        $joinTable = $config->getJoinTable();

        foreach ($table->associations() as $association) {
            // skip unsupported associations
            if (! in_array($association->type(), ['manyToMany', 'manyToOne'])) {
                continue;
            }

            if ($association->className() !== $joinTable->getAlias()) {
                continue;
            }

            return $association;
        }

        throw new RuntimeException(sprintf(
            'Table "%s" has no association with "%s"',
            get_class($config->getTable()),
            $joinTable->getAlias()
        ));
    }
}

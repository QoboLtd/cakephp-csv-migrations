<?php
namespace CsvMigrations\Aggregator;

use Cake\Datasource\QueryInterface;
use RuntimeException;

final class AggregateResult
{
    /**
     * Aggregator execution method.
     *
     * @param \CsvMigrations\Aggregator\AggregatorInterface $aggregator Aggregator instance
     * @return string
     */
    public static function get(AggregatorInterface $aggregator)
    {
        $config = $aggregator->getConfig();

        $query = $config->getTable()->find('all');
        $query = $aggregator->applyConditions($query);

        if ($config->joinMode()) {
            $query = static::join($aggregator, $query);
        }

        if ($query->isEmpty()) {
            return '';
        }

        return (string)$aggregator->getResult($query->first());
    }

    /**
     * Aggregator query is joined whenever the entity instance is set, meaning
     * that the results will be limited to the entity's associated records.
     *
     * @param \CsvMigrations\Aggregator\AggregatorInterface $aggregator Aggregator instance
     * @param \Cake\Datasource\QueryInterface $query Query object
     * @return \Cake\Datasource\QueryInterface
     */
    private static function join(AggregatorInterface $aggregator, QueryInterface $query)
    {
        $config = $aggregator->getConfig();
        $association = static::findAssociation($aggregator);

        // limit to record's associated data
        $query->innerJoinWith($association->getName(), function ($q) use ($association, $config) {
            $primaryKey = $config->getJoinTable()->getPrimaryKey();

            return $q->where([
                $association->aliasField($primaryKey) => $config->getEntity()->get($primaryKey)
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
    private static function findAssociation(AggregatorInterface $aggregator)
    {
        $config = $aggregator->getConfig();

        foreach ($config->getTable()->associations() as $association) {
            // skip unsupported associations
            if (! in_array($association->type(), ['manyToMany', 'manyToOne'])) {
                continue;
            }

            if ($association->className() !== $config->getJoinTable()->getAlias()) {
                continue;
            }

            return $association;
        }

        throw new RuntimeException(sprintf(
            'Table "%s" has no association with "%s"',
            get_class($config->getTable()),
            $config->getJoinTable()->getAlias()
        ));
    }
}

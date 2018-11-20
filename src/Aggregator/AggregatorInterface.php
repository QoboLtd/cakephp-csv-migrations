<?php
namespace CsvMigrations\Aggregator;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;

interface AggregatorInterface
{
    /**
     * Validates aggregator config.
     *
     * @return bool
     */
    public function validate() : bool;

    /**
     * Applies aggregation conditions.
     *
     * @param \Cake\Datasource\QueryInterface $query Query instance
     * @return \Cake\Datasource\QueryInterface
     */
    public function applyConditions(QueryInterface $query) : QueryInterface;

    /**
     * Returns aggregated result.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @return mixed
     */
    public function getResult(EntityInterface $entity);

    /**
     * Configuration instance getter.
     *
     * @return \CsvMigrations\Aggregator\Configuration
     */
    public function getConfig() : Configuration;
}

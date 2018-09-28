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
    public function validate();

    /**
     * Applies aggregation conditions.
     *
     * @param \Cake\Datasource\QueryInterface $query Query instance
     * @return \Cake\Datasource\QueryInterface
     */
    public function applyConditions(QueryInterface $query);

    /**
     * Returns aggregated result.
     *
     * @param \Cake\Datasource\ResultSetInterface $resultSet ResultSet instance
     * @return mixed
     */
    public function getResult(EntityInterface $resultSet);
}

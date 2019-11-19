<?php

namespace CsvMigrations\Aggregator;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;
use Cake\ORM\Query;

final class LastAggregator extends AbstractAggregator
{
    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        return parent::validate();
    }

    /**
     * {@inheritDoc}
     */
    public function applyConditions(QueryInterface $query): QueryInterface
    {
        return $this->getConfig()->getField() === $this->getConfig()->getDisplayField() ?
            $this->applyConditionsWithMax($query) :
            $this->applyConditionsWithOrder($query);
    }

    /**
     * If the aggregated field and display field are NOT the same we are
     * using ordering on the aggregated field to retrieve the latest record.
     *
     * @param \Cake\Datasource\QueryInterface $query Query instance
     * @return \Cake\Datasource\QueryInterface
     * @link https://stackoverflow.com/a/19411219/2562232
     */
    private function applyConditionsWithOrder(QueryInterface $query): QueryInterface
    {
        $table = $this->getConfig()->getTable();

        $aggregateField = $table->aliasField($this->getConfig()->getField());

        $query->select($this->getConfig()->getDisplayField())
            ->order([$aggregateField => 'DESC']);

        return $query;
    }

    /**
     * For performance reasons, if the aggregated field and display field are the
     * same we are using the SQL MAX function to retrieve the latest record.
     *
     * @param \Cake\Datasource\QueryInterface $query Query instance
     * @return \Cake\Datasource\QueryInterface
     * @link https://stackoverflow.com/a/426785/2562232
     */
    private function applyConditionsWithMax(QueryInterface $query): QueryInterface
    {
        $table = $this->getConfig()->getTable();

        $aggregateField = $table->aliasField($this->getConfig()->getField());

        if ($query instanceof Query) {
            $query->select([$this->getConfig()->getDisplayField() => $query->func()->max($aggregateField)]);
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(EntityInterface $entity)
    {
        return $entity->get($this->getConfig()->getDisplayField());
    }
}

<?php
namespace CsvMigrations\Aggregator;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;

final class LastAggregator extends AbstractAggregator
{
    /**
     * {@inheritDoc}
     */
    public function validate()
    {
        return parent::validate();
    }

    /**
     * {@inheritDoc}
     */
    public function applyConditions(QueryInterface $query)
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
    private function applyConditionsWithOrder(QueryInterface $query)
    {
        $query->select($this->getConfig()->getDisplayField());
        $query->order([($this->getConfig()->getField()) => 'DESC']);

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
    private function applyConditionsWithMax(QueryInterface $query)
    {
        $query->select([
            $this->getConfig()->getDisplayField() => $query->func()->max($this->getConfig()->getField())
        ]);

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

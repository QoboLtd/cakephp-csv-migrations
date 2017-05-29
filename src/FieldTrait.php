<?php
namespace CsvMigrations;

use Cake\ORM\Query;
use CsvMigrations\ConfigurationTrait;

trait FieldTrait
{
    /**
     * Method that adds lookup fields with the id value to the Where clause in ORM Query
     *
     * @param  \Cake\ORM\Query $query Query instance
     * @param  string          $id    Record id
     * @return \Cake\ORM\Query
     */
    public function findByLookupFields(Query $query, $id)
    {
        $lookupFields = (array)$this->getConfig(ConfigurationTrait::$CONFIG_OPTION_LOOKUP_FIELDS);

        if (empty($lookupFields)) {
            return $query;
        }

        $tableName = $this->alias();
        // check for record by table's lookup fields
        foreach ($lookupFields as $lookupField) {
            // prepend table name to avoid CakePHP ORM's ambiguous column errors
            if (false === strpos($lookupField, '.')) {
                $lookupField = $tableName . '.' . $lookupField;
            }
            $query->orWhere([$lookupField => $id]);
        }

        return $query;
    }
}

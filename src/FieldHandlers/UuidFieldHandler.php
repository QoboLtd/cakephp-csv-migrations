<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseSimpleFieldHandler;

class UuidFieldHandler extends BaseSimpleFieldHandler
{
    /**
     * Field type
     */
    const DB_FIELD_TYPE = 'uuid';

    /**
     * Get search operators
     *
     * This method prepares a list of search operators that
     * are appropriate for a given field.
     *
     * @return array List of search operators
     */
    public function getSearchOperators()
    {
        return [
            'is' => [
                'label' => 'is',
                'operator' => 'IN',
            ],
            'is_not' => [
                'label' => 'is not',
                'operator' => 'NOT IN',
            ],
        ];
    }
}

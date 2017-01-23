<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class UuidFieldHandler extends BaseFieldHandler
{
    /**
     * Field type
     */
    const DB_FIELD_TYPE = 'uuid';

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

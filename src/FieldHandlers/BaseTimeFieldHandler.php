<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

/**
 * BaseTimeFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to date and time field handlers.
 */
abstract class BaseTimeFieldHandler extends BaseFieldHandler
{
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
            'greater' => [
                'label' => 'from',
                'operator' => '>',
            ],
            'less' => [
                'label' => 'to',
                'operator' => '<',
            ],
        ];
    }
}

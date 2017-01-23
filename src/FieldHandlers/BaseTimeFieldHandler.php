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

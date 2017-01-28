<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseSimpleFieldHandler;

abstract class BaseNumberFieldHandler extends BaseSimpleFieldHandler
{
    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'number';

    /**
     * Search operators
     *
     * @var array
     */
    public $searchOperators = [
        'is' => [
            'label' => 'is',
            'operator' => 'IN',
        ],
        'is_not' => [
            'label' => 'is not',
            'operator' => 'NOT IN',
        ],
        'greater' => [
            'label' => 'greater',
            'operator' => '>',
        ],
        'less' => [
            'label' => 'less',
            'operator' => '<',
        ],
    ];
}

<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseCombinedFieldHandler;

class MoneyFieldHandler extends BaseCombinedFieldHandler
{
    /**
     * Combined fields
     *
     * @var array
     */
    protected static $_fields = [
        'amount' => [
            'handler' => __NAMESPACE__ . '\\DecimalFieldHandler'
        ],
        'currency' => [
            'handler' => __NAMESPACE__ . '\\ListFieldHandler'
        ]
    ];
}

<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseCombinedFieldHandler;

class MetricFieldHandler extends BaseCombinedFieldHandler
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
        'unit' => [
            'handler' => __NAMESPACE__ . '\\ListFieldHandler'
        ]
    ];
}

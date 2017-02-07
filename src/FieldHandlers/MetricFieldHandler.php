<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseCombinedFieldHandler;

class MetricFieldHandler extends BaseCombinedFieldHandler
{
    /**
     * Set combined fields
     *
     * @return void
     */
    protected function _setCombinedFields()
    {
        $this->_fields = [
            'amount' => [
                'handler' => __NAMESPACE__ . '\\DecimalFieldHandler'
            ],
            'unit' => [
                'handler' => __NAMESPACE__ . '\\ListFieldHandler'
            ]
        ];
    }
}

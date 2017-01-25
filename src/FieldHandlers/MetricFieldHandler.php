<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseCombinedFieldHandler;

class MetricFieldHandler extends BaseCombinedFieldHandler
{
    /**
     * Field type match pattern
     */
    const FIELD_TYPE_PATTERN = '/metric\((.*?)\)/';

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

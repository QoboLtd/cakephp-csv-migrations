<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseCombinedFieldHandler;

class MetricFieldHandler extends BaseCombinedFieldHandler
{
    /**
     * {@inheritDoc}
     */
    const FIELD_TYPE_PATTERN = '/metric\((.*?)\)/';

    /**
     * {@inheritDoc}
     */
    protected function _setCombinedFields()
    {
        $this->_fields = [
            'amount' => [
                'type' => 'decimal',
                'handler' => __NAMESPACE__ . '\\DecimalFieldHandler',
                'field' => 'input'
            ],
            'unit' => [
                'type' => 'string',
                'handler' => __NAMESPACE__ . '\\ListFieldHandler',
                'field' => 'select'
            ]
        ];
    }
}

<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseCombinedFieldHandler;

class MoneyFieldHandler extends BaseCombinedFieldHandler
{
    /**
     * {@inheritDoc}
     */
    const FIELD_TYPE_PATTERN = '/money\((.*?)\)/';

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
            'currency' => [
                'type' => 'string',
                'handler' => __NAMESPACE__ . '\\ListFieldHandler',
                'field' => 'select'
            ]
        ];
    }
}

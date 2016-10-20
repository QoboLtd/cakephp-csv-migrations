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
                'handler' => __NAMESPACE__ . '\\DecimalFieldHandler',
                'field' => 'input'
            ],
            'currency' => [
                'handler' => __NAMESPACE__ . '\\ListFieldHandler',
                'field' => 'select'
            ]
        ];
    }
}

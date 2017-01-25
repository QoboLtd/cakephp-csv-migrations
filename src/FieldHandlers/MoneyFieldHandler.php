<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseCombinedFieldHandler;

class MoneyFieldHandler extends BaseCombinedFieldHandler
{
    /**
     * Field type match pattern
     */
    const FIELD_TYPE_PATTERN = '/money\((.*?)\)/';

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
            'currency' => [
                'handler' => __NAMESPACE__ . '\\ListFieldHandler'
            ]
        ];
    }
}

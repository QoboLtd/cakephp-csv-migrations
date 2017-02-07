<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseCombinedFieldHandler;

class MoneyFieldHandler extends BaseCombinedFieldHandler
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
            'currency' => [
                'handler' => __NAMESPACE__ . '\\ListFieldHandler'
            ]
        ];
    }
}

<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
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
            'currency' => [
                'type' => 'string',
                'field' => 'select'
            ],
            'amount' => [
                'type' => 'string',
                'field' => 'input'
            ]
        ];
    }
}

<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
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
                'type' => 'integer',
                'field' => 'input'
            ],
            'unit' => [
                'type' => 'string',
                'field' => 'select'
            ]
        ];
    }
}

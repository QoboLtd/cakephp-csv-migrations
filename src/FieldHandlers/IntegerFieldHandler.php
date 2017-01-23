<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class IntegerFieldHandler extends BaseFieldHandler
{
    /**
     * {@inheritDoc}
     */
    const DB_FIELD_TYPE = 'integer';

    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'number';

    /**
     * {@inheritDoc}
     */
    public function renderValue($data, array $options = [])
    {
        $result = (int)filter_var($data, FILTER_SANITIZE_NUMBER_INT);

        if (!empty($result) && is_numeric($result)) {
            $result = number_format($result);
        } else {
            $result = (string)$result;
        }

        return $result;
    }

    public function getSearchOperators()
    {
        return [
            'is' => [
                'label' => 'is',
                'operator' => 'IN',
            ],
            'is_not' => [
                'label' => 'is not',
                'operator' => 'NOT IN',
            ],
            'greater' => [
                'label' => 'greater',
                'operator' => '>',
            ],
            'less' => [
                'label' => 'less',
                'operator' => '<',
            ],
        ];
    }
}

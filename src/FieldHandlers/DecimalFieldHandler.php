<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class DecimalFieldHandler extends BaseFieldHandler
{
    /**
     * {@inheritDoc}
     */
    const DB_FIELD_TYPE = 'decimal';

    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if (!empty($result) && is_numeric($result)) {
            $result = number_format($result, 2);
        }

        return $result;
    }
}

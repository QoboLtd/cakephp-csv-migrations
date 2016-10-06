<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class IntegerFieldHandler extends BaseFieldHandler
{
    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = filter_var($data, FILTER_SANITIZE_NUMBER_INT);

        if (!empty($result) && is_numeric($result)) {
            $result = number_format($result);
        }

        return $result;
    }
}

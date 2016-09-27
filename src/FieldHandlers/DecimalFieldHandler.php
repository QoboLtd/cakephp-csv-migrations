<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class DecimalFieldHandler extends BaseFieldHandler
{
    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = $data;

        if (!empty($data) && is_numeric($data)) {
            $result = number_format($data, 2);
        }

        return $result;
    }
}

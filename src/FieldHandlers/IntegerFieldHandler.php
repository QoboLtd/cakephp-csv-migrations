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
}

<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseNumberFieldHandler;

class IntegerFieldHandler extends BaseNumberFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'integer';

    /**
     * Render field value
     *
     * This method prepares the output of the value for the given
     * field.  The result can be controlled via the variety of
     * options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field value
     */
    public function renderValue($data, array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $result = (int)filter_var($data, FILTER_SANITIZE_NUMBER_INT);

        if (!empty($result) && is_numeric($result)) {
            $result = number_format($result);
        } else {
            $result = (string)$result;
        }

        return $result;
    }
}

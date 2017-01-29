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
     * Sanitize field value
     *
     * This method filters the value and removes anything
     * potentially dangerous.  Ideally, it should always be
     * called before rendering the value to the user, in
     * order to avoid cross-site scripting (XSS) attacks.
     *
     * @throws \InvalidArgumentException when data is not a string
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field value
     */
    public function sanitizeValue($data, array $options = [])
    {
        $data = parent::sanitizeValue($data, $options);
        $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);

        return $data;
    }
}

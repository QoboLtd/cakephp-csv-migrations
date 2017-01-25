<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseSimpleFieldHandler;

class IntegerFieldHandler extends BaseSimpleFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'integer';

    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'number';

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

    /**
     * Get search operators
     *
     * This method prepares a list of search operators that
     * are appropriate for a given field.
     *
     * @return array List of search operators
     */
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

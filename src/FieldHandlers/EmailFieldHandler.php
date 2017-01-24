<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseSimpleFieldHandler;

class EmailFieldHandler extends BaseSimpleFieldHandler
{
    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'email';

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
        $options = array_merge($this->defaultOptions, $options);
        $result = filter_var($data, FILTER_SANITIZE_EMAIL);

        // Only link to valid emails, to avoid unpredictable behavior
        if (!empty($result) && filter_var($result, FILTER_VALIDATE_EMAIL)) {
            if (!isset($options['renderAs']) || !$options['renderAs'] === static::RENDER_PLAIN_VALUE) {
                $result = $this->cakeView->Html->link($result, 'mailto:' . $result, ['target' => '_blank']);
            }
        }

        return $result;
    }
}

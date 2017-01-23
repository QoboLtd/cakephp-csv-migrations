<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseSimpleFieldHandler;

class UrlFieldHandler extends BaseSimpleFieldHandler
{
    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'url';

    /**
     * Method that renders default type field's value.
     *
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($data, array $options = [])
    {
        $result = filter_var($data, FILTER_SANITIZE_URL);

        // Only link to URLs with schema, to avoid unpredictable behavior
        if (!empty($result) && filter_var($result, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
            if (!isset($options['renderAs']) || !$options['renderAs'] === static::RENDER_PLAIN_VALUE) {
                $result = $this->cakeView->Html->link($result, $result, ['target' => '_blank']);
            }
        }

        return $result;
    }
}

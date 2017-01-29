<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseStringFieldHandler;

class UrlFieldHandler extends BaseStringFieldHandler
{
    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'url';

    /**
     * Sanitize options
     *
     * Name of filter_var() filter to run and all desired
     * options/flags.
     *
     * @var array
     */
    public $sanitizeOptions = [FILTER_SANITIZE_URL];

    /**
     * Format field value
     *
     * This method provides a customization point for formatting
     * of the field value before rendering.
     *
     * NOTE: The value WILL NOT be sanitized during the formatting.
     *       It is assumed that sanitization happens either before
     *       or after this method is called.
     *
     * @param mixed $data    Field value data
     * @param array $options Field formatting options
     * @return string
     */
    protected function formatValue($data, array $options = [])
    {
        $result = (string)$data;

        if (empty($result)) {
            return $result;
        }

        if (array_key_exists('renderAs', $options) && ($options['renderAs'] === static::RENDER_PLAIN_VALUE)) {
            return $result;
        }

        // Only link to URLs with schema, to avoid unpredictable behavior
        if (filter_var($result, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) === false) {
            return $result;
        }

        $result = $this->cakeView->Html->link($result, $result, ['target' => '_blank']);

        return $result;
    }
}

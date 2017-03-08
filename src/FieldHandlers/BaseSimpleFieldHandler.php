<?php
namespace CsvMigrations\FieldHandlers;

/**
 * BaseSimpleFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to simple field handlers.
 */
abstract class BaseSimpleFieldHandler extends BaseFieldHandler
{
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
        return (string)$data;
    }

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
        $result = parent::renderValue($data, $options);

        if (!empty($options['renderAs']) && static::RENDER_PLAIN_VALUE === $options['renderAs']) {
            return $result;
        }

        $result = $this->formatValue($result, $options);

        return $result;
    }
}

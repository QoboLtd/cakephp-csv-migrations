<?php
namespace CsvMigrations\FieldHandlers\Renderer;

/**
 * NumberRenderer
 *
 * Render value as number
 */
class NumberRenderer extends BaseRenderer
{
    /**
     * Precision
     *
     * Temporary setting for decimal precision, until
     * we learn to read it from the fields.ini.
     *
     * @todo Replace with configuration from fields.ini
     */
    const PRECISION = 0;

    /**
     * Render value
     *
     * @param mixed $value Value to render
     * @param array $options Rendering options
     * @return string Text, HTML or other string result
     */
    public function renderValue($value, array $options = [])
    {
        $result = (float)$value;

        if (!empty($result) && is_numeric($result)) {
            $result = number_format($result, static::PRECISION);
        } else {
            $result = (string)$result;
        }

        return $result;
    }
}

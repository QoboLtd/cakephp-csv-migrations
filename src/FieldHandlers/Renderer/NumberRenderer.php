<?php
namespace CsvMigrations\FieldHandlers\Renderer;

use InvalidArgumentException;

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
     * @throws \InvalidArgumentException when sanitize fails
     * @param mixed $value Value to render
     * @param array $options Rendering options
     * @return string Text, HTML or other string result
     */
    public function renderValue($value, array $options = [])
    {
        // Sanitize
        $result = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if ($result === false) {
            throw new InvalidArgumentException("Failed to sanitize number");
        }

        $result = (float)$result;

        if (!empty($result) && is_numeric($result)) {
            $result = number_format($result, static::PRECISION);
        } else {
            $result = (string)$result;
        }

        return $result;
    }
}

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
     * Decimal precision
     */
    const PRECISION = 2;

    /**
     * Render value
     *
     * Supported options:
     *
     * * precision - integer value as to how many decimal points to render.
     *               Default: 2.
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

        if (!isset($options['precision'])) {
            $options['precision'] = static::PRECISION;
        }

        if (!empty($result) && is_numeric($result)) {
            $result = number_format($result, $options['precision']);
        } else {
            $result = (string)$result;
        }

        return $result;
    }
}

<?php
namespace CsvMigrations\FieldHandlers\Renderer;

use InvalidArgumentException;

/**
 * BooleanRenderer
 *
 * Render boolean value.
 */
class BooleanRenderer extends BaseRenderer
{
    /**
     * Render value
     *
     * Supported options:
     *
     * * valueLabels - array of two strings to use for labels.
     *                 Defaults: '0' for false, '1' for true.
     *
     * @param mixed $value Value to render
     * @param array $options Rendering options
     * @return string Text, HTML or other string result
     */
    public function renderValue($value, array $options = [])
    {
        $valueLabels = [
            0 => '0',
            1 => '1',
        ];

        if (empty($options['valueLabels'])) {
            $options['valueLabels'] = $valueLabels;
        }

        if (!is_array($options['valueLabels'])) {
            throw new InvalidArgumentException("valueLabels option is not an array");
        }

        if (count($options['valueLabels']) < 2) {
            throw new InvalidArgumentException("valueLabels option has insufficient items");
        }

        $result = $value ? $options['valueLabels'][1] : $options['valueLabels'][0];
        $result = (string)$result;

        return $result;
    }
}

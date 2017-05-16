<?php
namespace CsvMigrations\FieldHandlers\Renderer;

/**
 * BooleanOnOffRenderer
 *
 * Render boolean value as On or Off string.
 */
class BooleanOnOffRenderer extends BooleanRenderer
{
    /**
     * Render value
     *
     * Supported options:
     *
     * * valueLabels - array of two strings to use for labels.
     *                 Defaults: 'Off' for false, 'On' for true.
     *
     * @param mixed $value Value to render
     * @param array $options Rendering options
     * @return string Text, HTML or other string result
     */
    public function renderValue($value, array $options = [])
    {
        $valueLabels = [
            0 => __('Off'),
            1 => __('On'),
        ];

        if (empty($options['valueLabels'])) {
            $options['valueLabels'] = $valueLabels;
        }
        $result = parent::renderValue($value, $options);

        return $result;
    }
}

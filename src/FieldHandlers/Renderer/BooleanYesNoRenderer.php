<?php
namespace CsvMigrations\FieldHandlers\Renderer;

/**
 * BooleanYesNoRenderer
 *
 * Render boolean value as Yes or No string.
 */
class BooleanYesNoRenderer extends BooleanRenderer
{
    /**
     * Render value
     *
     * Supported options:
     *
     * * valueLabels - array of two strings to use for labels.
     *                 Defaults: 'No' for false, 'Yes' for true.
     *
     * @param mixed $value Value to render
     * @param array $options Rendering options
     * @return string Text, HTML or other string result
     */
    public function renderValue($value, array $options = [])
    {
        $valueLabels = [
            0 => __('No'),
            1 => __('Yes'),
        ];

        if (empty($options['valueLabels'])) {
            $options['valueLabels'] = $valueLabels;
        }
        $result = parent::renderValue($value, $options);

        return $result;
    }
}

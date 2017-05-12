<?php
namespace CsvMigrations\FieldHandlers\Renderer;

/**
 * BooleanYesNoRenderer
 *
 * Render boolean value as Yes or No string.
 */
class BooleanYesNoRenderer extends BaseRenderer
{
    /**
     * Render value
     *
     * @param mixed $value Value to render
     * @param array $options Rendering options
     * @return string Text, HTML or other string result
     */
    public function renderValue($value, array $options = [])
    {
        $result = $value ? __('Yes') : __('No');

        return $result;
    }
}

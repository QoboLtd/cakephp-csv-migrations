<?php
namespace CsvMigrations\FieldHandlers\Renderer;

/**
 * UrlRenderer
 *
 * Render value as a linkable URL
 */
class UrlRenderer extends BaseRenderer
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
        $result = (string)$value;

        if (empty($result)) {
            return $result;
        }

        // Only link to URLs with schema, to avoid unpredictable behavior
        if (filter_var($result, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) === false) {
            return $result;
        }

        $result = $this->view->Html->link($result, $result, ['target' => '_blank']);

        return $result;
    }
}

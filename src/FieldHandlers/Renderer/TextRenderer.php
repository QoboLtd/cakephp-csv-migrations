<?php
namespace CsvMigrations\FieldHandlers\Renderer;

/**
 * TextRenderer
 *
 * Render value as paragraphed text
 */
class TextRenderer extends BaseRenderer
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

        // Auto-paragraph
        $result = $this->view->Text->autoParagraph($result);

        return $result;
    }
}

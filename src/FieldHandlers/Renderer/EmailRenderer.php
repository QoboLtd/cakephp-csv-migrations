<?php
namespace CsvMigrations\FieldHandlers\Renderer;

use RuntimeException;

/**
 * EmailRenderer
 *
 * Render value as a linkable email URL
 */
class EmailRenderer extends BaseRenderer
{
    /**
     * Render value
     *
     * @throws \RuntimeException when sanitize fails
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

        // Sanitize
        $result = filter_var($result, FILTER_SANITIZE_EMAIL);
        if ($result === false) {
            throw new RuntimeException("Failed to sanitize email");
        }


        // Only link to valid emails, to avoid unpredictable behavior
        if (filter_var($result, FILTER_VALIDATE_EMAIL) === false) {
            return $result;
        }

        $result = $this->view->Html->link($result, 'mailto:' . $result, ['target' => '_blank']);

        return $result;
    }
}

<?php
namespace CsvMigrations\FieldHandlers\Renderer;

use InvalidArgumentException;

/**
 * UrlRenderer
 *
 * Render value as a linkable URL
 */
class UrlRenderer extends BaseRenderer
{
    /**
     * Default link target
     */
    const TARGET = '_blank';

    /**
     * Render value
     *
     * Supported options:
     *
     * * linkTarget - string for link target (_self, _blank, etc).
     *                Default: '_blank'.
     *
     * @throws \InvalidArgumentException when sanitize fails
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

        if (!isset($options['linkTarget'])) {
            $options['linkTarget'] = static::TARGET;
        }

        // Sanitize
        $result = filter_var($result, FILTER_SANITIZE_URL);
        if ($result === false) {
            // If you find a case where FILTER_SANITIZE_URL fails, add
            // a unit test to UrlRendererTest and remove annotations here.
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException("Failed to sanitize URL");
            // @codeCoverageIgnoreEnd
        }

        // Only link to URLs with schema, to avoid unpredictable behavior
        if (filter_var($result, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) === false) {
            return $result;
        }

        $result = $this->view->Html->link($result, $result, ['target' => $options['linkTarget']]);

        return $result;
    }
}

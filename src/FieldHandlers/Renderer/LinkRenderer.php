<?php
namespace CsvMigrations\FieldHandlers\Renderer;

use InvalidArgumentException;

/**
 * LinkRenderer
 *
 * Render value as a link to custom URL
 */
class LinkRenderer extends BaseRenderer
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
     * * linkTo     - string for link URL (use %s as placeholder).
     *                Default: none.
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

        if (!isset($options['linkTo'])) {
            $options['linkTo'] = '';
        }

        $options['linkTo'] = sprintf($options['linkTo'], rawurlencode($value));

        // Sanitize
        $options['linkTo'] = filter_var($options['linkTo'], FILTER_SANITIZE_URL);
        if ($result === false) {
            // If you find a case where FILTER_SANITIZE_URL fails, add
            // a unit test to LinkRendererTest and remove annotations here.
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException("Failed to sanitize link URL");
            // @codeCoverageIgnoreEnd
        }
        $value = filter_var($value, FILTER_SANITIZE_STRING);
        if ($result === false) {
            // If you find a case where FILTER_SANITIZE_STRING fails, add
            // a unit test to LinkRendererTest and remove annotations here.
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException("Failed to sanitize string");
            // @codeCoverageIgnoreEnd
        }

        // Only link to URLs with schema, to avoid unpredictable behavior
        if (filter_var($options['linkTo'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) === false) {
            return $result;
        }

        $result = $this->view->Html->link($value, $options['linkTo'], ['target' => $options['linkTarget']]);

        return $result;
    }
}

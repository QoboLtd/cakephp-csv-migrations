<?php
namespace CsvMigrations\FieldHandlers\Renderer;

use CsvMigrations\View\AppView;
use InvalidArgumentException;

/**
 * BaseRenderer
 *
 * Base renderer provides the default rendering
 * functionality.
 */
abstract class BaseRenderer implements RendererInterface
{
    protected $view;

    /**
     * Constructor
     *
     * @param object $view Optional instance of the App View
     * @return object
     */
    public function __construct($view = null)
    {
        $this->view = $view ? $view : new AppView();
    }

    /**
     * Render value
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

        // Sanitize
        $result = filter_var($result, FILTER_SANITIZE_STRING);
        if ($result === false) {
            // If you find a case where FILTER_SANITIZE_STRING fails, add
            // a unit test to StringRendererTest/PlainRendererTest and
            // remove annotations here.
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException("Failed to sanitize string");
            // @codeCoverageIgnoreEnd
        }

        return $result;
    }
}

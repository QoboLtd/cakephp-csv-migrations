<?php
namespace CsvMigrations\FieldHandlers\Renderer;

use CsvMigrations\View\AppView;

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
     * @param mixed $value Value to render
     * @param array $options Rendering options
     * @return string Text, HTML or other string result
     */
    public function renderValue($value, array $options = [])
    {
        $result = (string)$value;

        return $result;
    }
}

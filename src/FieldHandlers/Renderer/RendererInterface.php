<?php
namespace CsvMigrations\FieldHandlers\Renderer;

/**
 * RendererInterface
 *
 * Renderer interface defines the contract for all
 * rendering classes.
 */
interface RendererInterface
{
    /**
     * Constructor
     *
     * @param object $view Optional instance of the App View
     * @return object
     */
    public function __construct($view = null);

    /**
     * Render value
     *
     * @param mixed $value Value to render
     * @param array $options Rendering options
     * @return string Text, HTML or other string result
     */
    public function renderValue($value, array $options = []);
}

<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
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

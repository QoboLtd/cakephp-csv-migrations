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

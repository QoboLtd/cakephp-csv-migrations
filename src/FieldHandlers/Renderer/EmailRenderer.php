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

use InvalidArgumentException;

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
        $result = filter_var($result, FILTER_SANITIZE_EMAIL);
        if ($result === false) {
            // If you find a case where FILTER_SANITIZE_EMAIL fails, add
            // a unit test to EmailRendererTest and remove annotations here.
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException("Failed to sanitize email");
            // @codeCoverageIgnoreEnd
        }

        // Only link to valid emails, to avoid unpredictable behavior
        if (filter_var($result, FILTER_VALIDATE_EMAIL) === false) {
            return $result;
        }

        $result = $this->view->Html->link($result, 'mailto:' . $result, ['target' => '_blank']);

        return $result;
    }
}

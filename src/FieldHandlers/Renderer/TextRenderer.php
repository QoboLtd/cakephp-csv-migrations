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
        $result = parent::renderValue($value, $options);

        if (empty($result)) {
            return $result;
        }

        // Auto-paragraph
        $result = $this->view->Text->autoParagraph($result);

        return $result;
    }
}

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
namespace CsvMigrations\FieldHandlers\Provider\RenderValue;

/**
 * TextRenderer
 *
 * Render value as paragraphed text
 */
class TextRenderer extends AbstractRenderer
{
    /**
     * Provide rendered value
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $result = parent::provide($data, $options);

        if (empty($result)) {
            return $result;
        }

        // Auto-paragraph
        $view = $this->config->getView();
        $result = $view->Text->autoParagraph($result);

        return $result;
    }
}

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

use CsvMigrations\FieldHandlers\FieldHandler;

/**
 * CombinedRenderer
 *
 * Render value of combined field
 */
class CombinedRenderer extends AbstractRenderer
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
        $combinedFields = $this->config->getProvider('combinedFields');
        $combinedFields = new $combinedFields($this->config);
        $combinedFields = $combinedFields->provide($data, $options);

        $result = [];
        $view = $this->config->getView();
        foreach ($combinedFields as $suffix => $fieldOptions) {
            $fieldName = $this->config->getField() . '_' . $suffix;

            if (empty($data)) {
                $data = $options['entity'];
            }
            $config = new $fieldOptions['config']($fieldName, $this->config->getTable());
            $config->setView($view);

            $handler = new FieldHandler($config);
            $result[] = $handler->renderValue($data, $options);
        }

        if (empty($result[0])) {
            return '';
        }

        return implode('&nbsp;', array_filter($result));
    }
}

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
 * CoordinatesRenderer
 *
 * Coordinates renderer provides custom render element.
 */
class CoordinatesRenderer extends AbstractRenderer
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

        $elementName = 'CsvMigrations.FieldHandlers/CoordinatesFieldHandler/view';
        $params = [
            'options' => $options,
            'result' => $result,
        ];

        return $this->renderElement($elementName, $params);
    }
}

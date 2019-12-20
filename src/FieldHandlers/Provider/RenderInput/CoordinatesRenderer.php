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

namespace CsvMigrations\FieldHandlers\Provider\RenderInput;

use Cake\Core\Configure;

/**
 * CoordinatesRenderer
 *
 * Coordinates renderer provides the functionality
 * for rendering a google map div with a dynamic marker
 * to pick up geographic coordinate.
 */
class CoordinatesRenderer extends AbstractRenderer
{
    /**
     * Provide
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $field = $this->config->getField();
        $table = $this->config->getTable();

        $fieldName = $table->aliasField($field);

        $default_coordinates = Configure::read("CsvMigrations.GoogleMaps.DefaultLocation");
        $options['attributes']["default_coordinates"] = !empty($default_coordinates) ? $default_coordinates : "0,0.0,0";

        $params = [
            'field' => $field,
            'name' => $fieldName,
            'type' => 'text',
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'extraClasses' => (!empty($options['extraClasses']) ? implode(' ', $options['extraClasses']) : ''),
            'attributes' => empty($options['attributes']) ? [] : $options['attributes'],
            'placeholder' => (!empty($options['placeholder']) ? $options['placeholder'] : ''),
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/CoordinatesFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);
    }
}

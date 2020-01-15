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

use CsvMigrations\FieldHandlers\Setting;

/**
 * IntegerRenderer
 *
 * Integer renderer provides the functionality
 * for rendering integer inputs.
 */
class IntegerRenderer extends AbstractRenderer
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

        $params = [
            'field' => $field,
            'name' => $fieldName,
            'type' => 'number',
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'step' => $options['step'] ?? Setting::DEFAULT_STEP_FOR_NUMBER,
            'max' => $options['max'] ?? Setting::MAX_VALUE_FOR_NUMBER,
            'min' => $options['min'] ?? Setting::MIN_VALUE_FOR_NUMBER,
            'extraClasses' => (!empty($options['extraClasses']) ? implode(' ', $options['extraClasses']) : ''),
            'attributes' => empty($options['attributes']) ? [] : $options['attributes'],
            'placeholder' => (!empty($options['placeholder']) ? $options['placeholder'] : ''),
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/IntegerFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);
    }
}

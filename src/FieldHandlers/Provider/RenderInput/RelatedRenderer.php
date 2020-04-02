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

use CsvMigrations\FieldHandlers\RelatedFieldTrait;

/**
 * RelatedRenderer
 *
 * Related renderer provides the functionality
 * for rendering related inputs.
 */
class RelatedRenderer extends AbstractRenderer
{
    use RelatedFieldTrait;

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

        $relatedProperties = $this->_getRelatedProperties($options['fieldDefinitions']->getLimit(), (string)$data);
        if (!empty($relatedProperties['dispFieldVal']) && !empty($relatedProperties['config']['parent']['module'])) {
            $relatedParentProperties = $this->_getRelatedParentProperties($relatedProperties);
            if (!empty($relatedParentProperties['dispFieldVal'])) {
                $relatedProperties['dispFieldVal'] = implode(' ' . $this->_separator . ' ', [
                    $relatedParentProperties['dispFieldVal'],
                    $relatedProperties['dispFieldVal'],
                ]);
            }
        }

        $params = [
            'field' => $field,
            'name' => $fieldName,
            'type' => 'select',
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'options' => [$data => $relatedProperties['dispFieldVal']],
            'relatedProperties' => $relatedProperties,
            'embedded' => isset($options['embeddedModal']) ? (bool)$options['embeddedModal'] : false,
            'icon' => $this->_getInputIcon($relatedProperties),
            'title' => (!empty($options['placeholder']) ? $options['placeholder'] : $this->_getInputHelp($relatedProperties)),
            'extraClasses' => (!empty($options['extraClasses']) ? implode(' ', $options['extraClasses']) : ''),
            'attributes' => empty($options['attributes']) ? [] : $options['attributes'],
            'help' => (!empty($options['help']) ? $options['help'] : ''),
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/RelatedFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);
    }
}

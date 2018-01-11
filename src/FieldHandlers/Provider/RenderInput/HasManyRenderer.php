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
 * HasManyRenderer
 *
 * HasMany renderer provides the functionality
 * for rendering HasMany inputs.
 */
class HasManyRenderer extends AbstractRenderer
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

        $relatedProperties = $this->_getRelatedProperties($options['fieldDefinitions']->getLimit(), $data);
        if (!empty($relatedProperties['dispFieldVal']) && !empty($relatedProperties['config']['parent']['module'])) {
            $relatedParentProperties = $this->_getRelatedParentProperties($relatedProperties);
            if (!empty($relatedParentProperties['dispFieldVal'])) {
                $relatedProperties['dispFieldVal'] = implode(' ' . $this->_separator . ' ', [
                    $relatedParentProperties['dispFieldVal'],
                    $relatedProperties['dispFieldVal']
                ]);
            }
        }

        $params = [
            'field' => $field,
            'name' => $options['associated_table_name'],
            'type' => 'select',
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'relatedProperties' => $relatedProperties,
            'embedded' => !empty($options['emDataTarget']) ? $options['emDataTarget'] : $field,
            'icon' => $this->_getInputIcon($relatedProperties),
            'title' => $this->_getInputHelp($relatedProperties),
            'extraClasses' => (!empty($options['extraClasses']) ? implode(' ', $options['extraClasses']) : ''),
            'attributes' => empty($options['attributes']) ? [] : $options['attributes'],
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/HasManyFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);
    }
}

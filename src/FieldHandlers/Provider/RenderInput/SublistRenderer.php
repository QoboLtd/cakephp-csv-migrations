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

/**
 * SublistRenderer
 *
 * Sublist renderer provides the functionality
 * for rendering sublist inputs.
 */
class SublistRenderer extends AbstractRenderer
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

        $selectListItems = $this->config->getProvider('selectOptions');
        $selectListItems = new $selectListItems($this->config);
        $listName = $options['fieldDefinitions']->getLimit();
        $listOptions = ['spacer' => null, 'flatten' => false];
        $fieldOptions = $selectListItems->provide($listName, $listOptions);

        $listOptions = ['spacer' => ''];
        $optionValues = $selectListItems->provide($listName, $listOptions);
        $structure = $this->_dynamicSelectStructure($fieldOptions);

        $params = [
            'field' => $field,
            'name' => $fieldName,
            'type' => 'select',
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'optionValues' => $optionValues,
            'structure' => $structure,
            'extraClasses' => (!empty($options['extraClasses']) ? implode(' ', $options['extraClasses']) : ''),
            'attributes' => empty($options['attributes']) ? [] : $options['attributes'],
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/SublistFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);
    }

    /**
     * Converts list options to supported dynamiSelect lib structure
     *
     * @link https://github.com/sorites/dynamic-select
     * @param array $options List options
     * @return array
     */
    protected function _dynamicSelectStructure($options)
    {
        $result = [];
        foreach ($options as $k => $v) {
            $result[$v['name']] = !empty($v['children']) ? $this->_dynamicSelectStructure($v['children']) : [];
        }

        return $result;
    }
}

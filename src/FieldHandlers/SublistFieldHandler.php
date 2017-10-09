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
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseCsvListFieldHandler;

class SublistFieldHandler extends BaseCsvListFieldHandler
{
    /**
     * Field type
     */
    const INPUT_FIELD_TYPE = 'select';

    /**
     * Render field input
     *
     * This method prepares the form input for the given field,
     * including the input itself, label, pre-populated value,
     * and so on.  The result can be controlled via the variety
     * of options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field input HTML
     */
    public function renderInput($data = '', array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $data = $this->_getFieldValueFromData($data);
        if (empty($data) && !empty($options['default'])) {
            $data = $options['default'];
        }

        $fieldName = $this->table->aliasField($this->field);

        $fieldOptions = $this->_getSelectOptions($options['fieldDefinitions']->getLimit(), null, false);
        $optionValues = $this->_getSelectOptions($options['fieldDefinitions']->getLimit(), '');
        $structure = $this->_dynamicSelectStructure($fieldOptions);

        $params = [
            'field' => $this->field,
            'name' => $fieldName,
            'type' => static::INPUT_FIELD_TYPE,
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'optionValues' => $optionValues,
            'structure' => $structure
        ];

        return $this->_renderElement(__FUNCTION__, $params, $options);
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

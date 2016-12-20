<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;
use CsvMigrations\ListTrait;

class SublistFieldHandler extends ListFieldHandler
{
    use ListTrait;

    const JS_SELECTORS = "$('%s').val('%s').change();";

    /**
     * Method responsible for rendering field's input.
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field input
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $data = $this->_getFieldValueFromData($field, $data);
        $fieldOptions = $this->_getSelectOptions($options['fieldDefinitions']->getLimit(), null, false);
        $optionValues = $this->_getSelectOptions($options['fieldDefinitions']->getLimit(), '');
        $structure = $this->_dynamicSelectStructure($fieldOptions);

        $levels = 0;
        // get nesting level based on dot notation
        foreach ($optionValues as $k => $v) {
            $count = substr_count($k, '.');
            if ($count <= $levels) {
                continue;
            }
            $levels = $count;
        }

        $inputOptions = [
            'type' => 'select',
            'required' => (bool)$options['fieldDefinitions']->getRequired()
        ];

        $html = '';
        $selectors = [];
        for ($i = 0; $i <= $levels; $i++) {
            $inputOptions['data-type'] = 'dynamic-select-' . $field . '_' . $i;
            $selectors[] = '[data-type="' . $inputOptions['data-type'] . '"]';
            $html .= $this->cakeView->Form->input(
                $this->_getFieldName($table, $field, $options),
                0 === $i ? $inputOptions : array_merge(['label' => false], $inputOptions)
            );
        }

        // edit mode
        $defaultOptions = '';
        if (!empty($data)) {
            $parts = explode('.', $data);
            $count = count($parts);
            for ($i = 0; $i <= $levels; $i++) {
                $length = $i + 1;
                // stop when current level exceeds data parts
                // example: level 3 and data is 'foo.bar' (parts is ['foo', 'bar'])
                if ($length > $count) {
                    break;
                }
                $defaultOptions .= sprintf(
                    static::JS_SELECTORS,
                    // selector id
                    $selectors[$i],
                    // target value based on current level
                    // example: level 1 and data is 'foo.bar', then current value will be 'foo'
                    implode('.', array_slice($parts, 0, $length))
                );
            }
        }

        return [
            'html' => $html,
            'post' => [
                'type' => 'scriptBlock',
                'content' => '$(document).dynamicSelect({
                    structure: ' . json_encode($structure) . ',
                    optionValues: ' . json_encode(array_flip($optionValues)) . ',
                    selectors: ' . json_encode($selectors) . ',
                    hideNext: true,
                    previousDefaultValue: true
                });' . $defaultOptions,
                'block' => 'scriptBottom'
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function renderSearchInput($table, $field, array $options = [])
    {
        return false;
    }

    /**
     * Converts list options to supported dynamiSelect lib structure (see link).
     *
     * @param array $options List options
     * @return array
     * @link https://github.com/sorites/dynamic-select
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

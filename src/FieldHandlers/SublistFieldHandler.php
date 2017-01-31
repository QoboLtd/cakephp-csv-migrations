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

        // get selectors
        $selectors = [];
        for ($i = 0; $i <= $levels; $i++) {
            $selectors[] = '[data-target="' . 'dynamic-select-' . $field . '_' . $i . '"]';
        }

        // default input options
        $defaultOptions = [
            'type' => 'select',
            'required' => (bool)$options['fieldDefinitions']->getRequired()
        ];

        // edit mode
        if (!empty($data)) {
            $data = explode('.', $data);
            $count = count($data);
        }

        // get inputs
        $inputs = [];
        for ($i = 0; $i <= $levels; $i++) {
            $inputOptions = $defaultOptions;
            $inputOptions['data-target'] = 'dynamic-select-' . $field . '_' . $i;
            if (0 === $i) {
                $inputOptions['data-type'] = 'dynamic-select';
                $inputOptions['data-structure'] = json_encode($structure);
                $inputOptions['data-option-values'] = json_encode(array_flip($optionValues));
                $inputOptions['data-selectors'] = json_encode($selectors);
                $inputOptions['data-hide-next'] = true;
                $inputOptions['data-previous-default-value'] = true;
            } else {
                $inputOptions['label'] = false;
            }
            // edit mode
            if (!empty($data) && ($i + 1) <= $count) {
                $inputOptions['data-value'] = implode('.', array_slice($data, 0, $i + 1));
            }
            $inputs[] = $this->cakeView->Form->input(
                $this->_getFieldName($table, $field, $options),
                $inputOptions
            );
        }

        return implode('', $inputs);
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

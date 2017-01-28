<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;
use CsvMigrations\ListTrait;

class SublistFieldHandler extends ListFieldHandler
{
    use ListTrait;

    const JS_SELECTORS = "$('%s').val('%s').change();";

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
            $selectors[] = '[data-target="' . 'dynamic-select-' . $this->field . '_' . $i . '"]';
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
            $inputOptions['data-target'] = 'dynamic-select-' . $this->field . '_' . $i;
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
                $this->table->aliasField($this->field),
                $inputOptions
            );
        }

        return implode('', $inputs);
    }

    /**
     * Render field search input
     *
     * This method prepares the search form input for the given field,
     * including the input itself, label, pre-populated value,
     * and so on.  The result can be controlled via the variety
     * of options.
     *
     * @param  array  $options Field options
     * @return array           Array of field input HTML, pre and post CSS, JS, etc
     */
    public function renderSearchInput(array $options = [])
    {
        return [];
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

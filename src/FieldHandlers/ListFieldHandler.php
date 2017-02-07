<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseCsvListFieldHandler;

class ListFieldHandler extends BaseCsvListFieldHandler
{
    /**
     * Input field html markup
     */
    const INPUT_HTML = '<div class="form-group">%s</div>';

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
        $fieldOptions = $this->_getSelectOptions($options['fieldDefinitions']->getLimit());

        $fieldName = $this->table->aliasField($this->field);

        $input = '';
        $input .= $options['label'] ? $this->cakeView->Form->label($fieldName, $options['label']) : '';

        $input .= $this->cakeView->Form->select($fieldName, $fieldOptions, [
            'class' => 'form-control',
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
            'value' => $data
        ]);

        return sprintf(static::INPUT_HTML, $input);
    }
}

<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class BooleanFieldHandler extends BaseFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'boolean';

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

        $fieldName = $this->_getFieldName($table, $field, $options);
        $label = $this->cakeView->Form->label($fieldName);
        $input = $this->cakeView->Form->input($fieldName, [
            'type' => 'checkbox',
            'class' => 'square',
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
            'checked' => $data,
            'label' => false,
            'templates' => [
                'inputContainer' => '<div class="{{required}}">' . $label . '<div class="clearfix"></div>{{content}}</div>'
            ]
        ]);

        return $input;
    }

    /**
     * Method that renders specified field's value based on the field's type.
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $data = $this->_getFieldValueFromData($field, $data);
        $result = $data ? __('Yes') : __('No');

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function renderSearchInput($table, $field, array $options = [])
    {
        $fieldType = $options['fieldDefinitions']->getType();

        if (in_array($fieldType, array_keys($this->_fieldTypes))) {
            $fieldType = $this->_fieldTypes[$fieldType];
        }

        $content = $this->cakeView->Form->input('{{name}}', [
            'type' => $fieldType,
            'label' => ''
        ]);

        return [
            'content' => $content
        ];
    }
}

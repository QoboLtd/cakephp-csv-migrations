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
     * Input field type
     */
    const INPUT_FIELD_TYPE = 'checkbox';

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

        return $this->cakeView->Form->input($this->_getFieldName($table, $field, $options), [
            'type' => static::INPUT_FIELD_TYPE,
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
            'checked' => $data
        ]);
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

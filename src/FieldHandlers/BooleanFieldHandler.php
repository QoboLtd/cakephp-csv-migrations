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
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field input
     */
    public function renderInput($data = '', array $options = [])
    {
        $data = $this->_getFieldValueFromData($data);

        $fieldName = $this->_getFieldName($options);
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
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($data, array $options = [])
    {
        $data = $this->_getFieldValueFromData($data);
        $result = $data ? __('Yes') : __('No');

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function renderSearchInput(array $options = [])
    {
        $fieldType = $options['fieldDefinitions']->getType();

        if (in_array($fieldType, array_keys($this->_fieldTypes))) {
            $fieldType = $this->_fieldTypes[$fieldType];
        }

        $content = $this->cakeView->Form->input('{{name}}', [
            'type' => 'checkbox',
            'class' => 'square',
            'label' => false
        ]);

        return [
            'content' => $content,
            'post' => [
                [
                    'type' => 'script',
                    'content' => [
                        'CsvMigrations.dom-observer',
                        'AdminLTE./plugins/iCheck/icheck.min',
                        'CsvMigrations.icheck.init'
                    ],
                    'block' => 'scriptBotton'
                ],
                [
                    'type' => 'css',
                    'content' => 'AdminLTE./plugins/iCheck/all',
                    'block' => 'css'
                ]
            ]
        ];
    }
}

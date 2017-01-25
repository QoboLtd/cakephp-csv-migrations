<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseSimpleFieldHandler;

class BooleanFieldHandler extends BaseSimpleFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'boolean';

    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'checkbox';

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
     * Render field value
     *
     * This method prepares the output of the value for the given
     * field.  The result can be controlled via the variety of
     * options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field value
     */
    public function renderValue($data, array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $data = $this->_getFieldValueFromData($data);
        $result = $data ? __('Yes') : __('No');

        return $result;
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
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $content = $this->cakeView->Form->input('{{name}}', [
            'type' => static::INPUT_FIELD_TYPE,
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

    /**
     * Get search operators
     *
     * This method prepares a list of search operators that
     * are appropriate for a given field.
     *
     * @return array List of search operators
     */
    public function getSearchOperators()
    {
        return [
            'is' => [
                'label' => 'is',
                'operator' => 'IN',
            ],
            'is_not' => [
                'label' => 'is not',
                'operator' => 'NOT IN',
            ],
        ];
    }
}

<?php
namespace CsvMigrations\FieldHandlers;

use Cake\I18n\Time;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

class TimeFieldHandler extends BaseFieldHandler
{
    /**
     * {@inheritDoc}
     */
    const DB_FIELD_TYPE = 'time';

    /**
     * Field type
     */
    const INPUT_FIELD_TYPE = 'timepicker';

    /**
     * Time format
     */
    const TIME_FORMAT = 'HH:mm';

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
        if ($data instanceof Time) {
            $data = $data->i18nFormat(static::TIME_FORMAT);
        }

        $required = false;
        if (isset($options['fieldDefinitions']) && is_object($options['fieldDefinitions'])) {
            $required = (bool)$options['fieldDefinitions']->getRequired();
        }
        $fieldName = $this->_getFieldName($options);

        if (isset($options['element'])) {
            $result = $this->cakeView->element($options['element'], [
                'options' => [
                    'fieldName' => $fieldName,
                    'type' => static::INPUT_FIELD_TYPE,
                    'label' => true,
                    'required' => $required,
                    'value' => $data
                ]
            ]);
        } else {
            $result = $this->cakeView->Form->input($fieldName, [
                'type' => 'text',
                'data-provide' => 'timepicker',
                'autocomplete' => 'off',
                'required' => $required,
                'value' => $data,
                'templates' => [
                    'input' => vsprintf($this->_templates['input'], [
                        'bootstrap-timepicker timepicker',
                        'clock-o'
                    ])
                ]
            ]);
        }

        return $result;
    }

    /**
     * Method that renders default type field's value.
     *
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($data, array $options = [])
    {
        $data = $this->_getFieldValueFromData($data);
        if (is_object($data)) {
            $result = $data->i18nFormat(static::TIME_FORMAT);
        } else {
            $result = $data;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function renderSearchInput(array $options = [])
    {
        if (isset($options['element'])) {
            $content = $this->cakeView->element($options['element'], [
                'options' => [
                    'fieldName' => '{{name}}',
                    'value' => '{{value}}',
                    'type' => static::INPUT_FIELD_TYPE,
                    'label' => false,
                ]
            ]);
        } else {
            $content = $this->cakeView->Form->input('', [
                'name' => '{{name}}',
                'value' => '{{value}}',
                'type' => 'text',
                'data-provide' => 'timepicker',
                'autocomplete' => 'off',
                'label' => false,
                'templates' => [
                    'input' => vsprintf($this->_templates['input'], [
                        'bootstrap-timepicker timepicker',
                        'clock-o'
                    ])
                ]
            ]);
        }

        return [
            'content' => $content,
            'post' => [
                [
                    'type' => 'script',
                    'content' => [
                        'CsvMigrations.dom-observer',
                        'AdminLTE./plugins/timepicker/bootstrap-timepicker.min',
                        'CsvMigrations.timepicker.init'
                    ],
                    'block' => 'scriptBotton'
                ],
                [
                    'type' => 'css',
                    'content' => 'AdminLTE./plugins/timepicker/bootstrap-timepicker.min',
                    'block' => 'css'
                ]
            ]
        ];
    }
}

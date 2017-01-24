<?php
namespace CsvMigrations\FieldHandlers;

use Cake\I18n\Time;
use CsvMigrations\FieldHandlers\BaseTimeFieldHandler;

class DatetimeFieldHandler extends BaseTimeFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'datetime';

    /**
     * Input field type
     */
    const INPUT_FIELD_TYPE = 'datetimepicker';

    /**
     * Datetime format
     */
    const DATETIME_FORMAT = 'yyyy-MM-dd HH:mm';

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
        $options = array_merge($this->defaultOptions, $options);
        $data = $this->_getFieldValueFromData($data);
        if ($data instanceof Time) {
            $data = $data->i18nFormat(static::DATETIME_FORMAT);
        }

        $required = false;
        if (isset($options['fieldDefinitions']) && is_object($options['fieldDefinitions'])) {
            $required = (bool)$options['fieldDefinitions']->getRequired();
        }
        $fieldName = $this->_getFieldName($options);

        if (isset($options['element'])) {
            return $this->cakeView->element($options['element'], [
                'options' => [
                    'fieldName' => $fieldName,
                    'type' => static::INPUT_FIELD_TYPE,
                    'label' => true,
                    'required' => $required,
                    'value' => $data
                ]
            ]);
        } else {
            return $this->cakeView->Form->input($fieldName, [
                'type' => 'text',
                'data-provide' => 'datetimepicker',
                'autocomplete' => 'off',
                'required' => $required,
                'value' => $data,
                'templates' => [
                    'input' => vsprintf($this->_templates['input'], [
                        '',
                        'calendar'
                    ])
                ]
            ]);
        }
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
        $options = array_merge($this->defaultOptions, $options);
        $data = $this->_getFieldValueFromData($data);
        if (is_object($data)) {
            $result = $data->i18nFormat(static::DATETIME_FORMAT);
        } else {
            $result = $data;
        }

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
        $options = array_merge($this->defaultOptions, $options);
        if (isset($options['element'])) {
            $content = $this->cakeView->element($options['element'], [
                'options' => [
                    'fieldName' => '{{name}}',
                    'value' => '{{value}}',
                    'type' => static::INPUT_FIELD_TYPE,
                    'label' => false
                ]
            ]);
        } else {
            $content = $this->cakeView->Form->input('', [
                'name' => '{{name}}',
                'value' => '{{value}}',
                'type' => 'text',
                'data-provide' => 'datetimepicker',
                'autocomplete' => 'off',
                'label' => false,
                'templates' => [
                    'input' => vsprintf($this->_templates['input'], [
                        '',
                        'calendar'
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
                        'AdminLTE./plugins/daterangepicker/moment.min',
                        'AdminLTE./plugins/daterangepicker/daterangepicker',
                        'CsvMigrations.datetimepicker.init'
                    ],
                    'block' => 'scriptBotton'
                ],
                [
                    'type' => 'css',
                    'content' => 'AdminLTE./plugins/daterangepicker/daterangepicker-bs3',
                    'block' => 'css'
                ]
            ]
        ];
    }
}

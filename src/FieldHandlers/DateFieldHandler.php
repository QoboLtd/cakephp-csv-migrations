<?php
namespace CsvMigrations\FieldHandlers;

use Cake\I18n\Date;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

class DateFieldHandler extends BaseFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'date';

    /**
     * Field type
     */
    const INPUT_FIELD_TYPE = 'datepicker';

    /**
     * Date format
     */
    const DATE_FORMAT = 'yyyy-MM-dd';

    /**
     * Javascript date format
     */
    const JS_DATE_FORMAT = 'yyyy-mm-dd';

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
        if ($data instanceof Date) {
            $data = $data->i18nFormat(static::DATE_FORMAT);
        }

        $required = false;
        if (isset($options['fieldDefinitions']) && is_object($options['fieldDefinitions'])) {
            $required = (bool)$options['fieldDefinitions']->getRequired();
        }
        $fieldName = $this->_getFieldName($table, $field, $options);

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
                'data-provide' => 'datepicker',
                'autocomplete' => 'off',
                'data-date-format' => static::JS_DATE_FORMAT,
                'data-date-autoclose' => true,
                'required' => $required,
                'value' => $data
            ]);
        }
    }

    /**
     * Method that renders default type field's value.
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
        if (is_object($data)) {
            $result = $data->i18nFormat(static::DATE_FORMAT);
        } else {
            $result = $data;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function renderSearchInput($table, $field, array $options = [])
    {
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
            $content = $this->cakeView->Form->input('{{name}}', [
                'value' => '{{value}}',
                'type' => 'text',
                'data-provide' => 'datepicker',
                'autocomplete' => 'off',
                'data-date-format' => static::JS_DATE_FORMAT,
                'data-date-autoclose' => true,
                'label' => false
            ]);
        }

        return [
            'content' => $content,
            'post' => [
                [
                    'type' => 'script',
                    'content' => 'AdminLTE./plugins/datepicker/bootstrap-datepicker',
                    'block' => 'scriptBotton'
                ],
                [
                    'type' => 'css',
                    'content' => 'AdminLTE./plugins/datepicker/datepicker3',
                    'block' => 'css'
                ]
            ]
        ];
    }
}

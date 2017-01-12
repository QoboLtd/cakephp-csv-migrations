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
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field input
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $data = $this->_getFieldValueFromData($field, $data);
        if ($data instanceof Time) {
            $data = $data->i18nFormat(static::TIME_FORMAT);
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
                'data-provide' => 'timepicker',
                'autocomplete' => 'off',
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
            $result = $data->i18nFormat(static::TIME_FORMAT);
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
        if (!isset($options['element']) && $this->cakeView->elementExists('QoboAdminPanel.datepicker')) {
            $options['element'] = 'QoboAdminPanel.datepicker';
        }

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
                'value' => '{{value}}',
                'type' => static::DB_FIELD_TYPE,
                'label' => false
            ]);
        }

        return [
            'content' => $content
        ];
    }
}

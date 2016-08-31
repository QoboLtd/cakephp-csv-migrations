<?php
namespace CsvMigrations\FieldHandlers;

use Cake\I18n\Date;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

class DatetimeFieldHandler extends BaseFieldHandler
{
    /**
     * Field type
     */
    const INPUT_FIELD_TYPE = 'datetimepicker';

    /**
     * Datetime format
     */
    const DATETIME_FORMAT = 'yyyy-MM-dd HH:mm';

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
        if ($data instanceof Date) {
            $data = $data->i18nFormat(static::DATETIME_FORMAT);
        }

        $required = (bool)$options['fieldDefinitions']->getRequired();
        $fieldName = $this->_getFieldName($table, $field, $options);

        if (!isset($options['element']) && $this->cakeView->elementExists('QoboAdminPanel.datepicker')) {
            $options['element'] = 'QoboAdminPanel.datepicker';
        }

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
                'type' => 'datetime',
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
        if (is_object($data)) {
            $result = $data->i18nFormat(static::DATETIME_FORMAT);
        } else {
            $result = $data;
        }

        return $result;
    }
}

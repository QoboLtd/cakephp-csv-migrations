<?php
namespace CsvMigrations\FieldHandlers;

use Cake\I18n\Date;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

class DateFieldHandler extends BaseFieldHandler
{
    /**
     * Field type
     */
    const FIELD_TYPE = 'datepicker';

    /**
     * Date format
     */
    const DATE_FORMAT = 'yyyy-MM-dd';

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
            $data = $data->i18nFormat(static::DATE_FORMAT);
        }

        return $this->cakeView->element('QoboAdminPanel.datepicker', [
            'options' => [
                'fieldName' => $this->_getFieldName($table, $field, $options),
                'type' => static::FIELD_TYPE,
                'label' => true,
                'required' => (bool)$options['fieldDefinitions']->getRequired(),
                'value' => $data
            ]
        ]);
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
            $result = $data->i18nFormat(static::DATE_FORMAT);
        } else {
            $result = $data;
        }

        return $result;
    }
}

<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

class TimeFieldHandler extends BaseFieldHandler
{
    const FIELD_TYPE = 'timepicker';

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
        // load AppView
        $cakeView = new AppView();

        return $cakeView->element('QoboAdminPanel.datepicker', [
            'options' => [
                'fieldName' => $this->_getFieldName($table, $field, $options),
                'type' => static::FIELD_TYPE,
                'label' => true,
                'required' => (bool)$options['fieldDefinitions']['required'],
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
            $result = $data->i18nFormat('HH:mm');
        } else {
            $result = $data;
        }

        return $result;
    }
}

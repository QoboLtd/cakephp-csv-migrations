<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

class BooleanFieldHandler extends BaseFieldHandler
{
    const FIELD_TYPE = 'checkbox';

    /**
     * Method responsible for rendering field's input.
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

        return $cakeView->Form->input($field, [
            'type' => static::FIELD_TYPE,
            'required' => (bool)$options['fieldDefinitions']['required'],
            'checked' => $data
        ]);
    }

    /**
     * Method that renders specified field's value based on the field's type.
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = $data ? __('Yes') : __('No');

        return $result;
    }
}

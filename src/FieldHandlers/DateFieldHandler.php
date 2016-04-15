<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

class DateFieldHandler extends BaseFieldHandler
{
    const FIELD_TYPE = 'datepicker';

    /**
     * Method responsible for rendering field's input.
     *
     * @param  string $plugin  plugin name
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field input
     */
    public function renderInput($plugin, $table, $field, $data = '', array $options = [])
    {
        // load AppView
        $cakeView = new AppView();

        return $cakeView->element('QoboAdminPanel.datepicker', [
            'options' => [
                'fieldName' => $field,
                'type' => static::FIELD_TYPE,
                'label' => true,
                'required' => (bool)$options['fieldDefinitions']['required'],
                'value' => $data
            ]
        ]);
    }
}

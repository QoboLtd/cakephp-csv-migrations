<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use CsvMigrations\FieldHandlers\FieldHandlerInterface;

abstract class BaseFieldHandler implements FieldHandlerInterface
{
    /**
     * Csv field types respective input field types
     * @var array
     */
    protected $_fieldTypes = [
        'text' => 'textarea',
        'string' => 'text',
        'uuid' => 'text',
        'integer' => 'number'
    ];

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

        $fieldType = $options['fieldDefinitions']['type'];

        if (in_array($fieldType, array_keys($this->_fieldTypes))) {
            $fieldType = $this->_fieldTypes[$fieldType];
        }

        return $cakeView->Form->input($this->_getFieldName($table, $field, $options), [
            'type' => $fieldType,
            'required' => (bool)$options['fieldDefinitions']['required'],
            'value' => $data
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
        $result = $data;

        return $result;
    }

    /**
     * Method responsible for converting field for migration.
     *
     * @param  string $field field name
     * @return array         converted field
     */
    public function fieldToDb($field)
    {
        $field['type'] = preg_replace(static::FIELD_TYPE_PATTERN, '', $field['type']);

        return $field;
    }

    /**
     * Method that generates field name based on its options.
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @param  string $field          Field name
     * @param  array  $options        Field options
     * @return string
     */
    protected function _getFieldName($table, $field, array $options = [])
    {
        if (isset($options['embedded'])) {
            return $options['embedded'] . '.' . $field;
        }

        return $table->alias() . '.' . $field;
    }
}

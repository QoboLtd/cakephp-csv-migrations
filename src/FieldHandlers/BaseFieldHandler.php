<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DbField;
use CsvMigrations\FieldHandlers\FieldHandlerInterface;
use CsvMigrations\View\AppView;

abstract class BaseFieldHandler implements FieldHandlerInterface
{
    /**
     * Default Database Field type
     */
    const DB_FIELD_TYPE = 'string';

    /**
     * CsvMigrations View instance.
     *
     * @var \CsvMigrations\View\AppView
     */
    public $cakeView;
    /**
     * Csv field types respective input field types
     * @var array
     */
    protected $_fieldTypes = [
        'text' => 'textarea',
        'blob' => 'textarea',
        'string' => 'text',
        'uuid' => 'text',
        'integer' => 'number',
        'url' => 'url',
        'email' => 'email',
        'phone' => 'tel',
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        // load AppView
        $this->cakeView = new AppView();
    }

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
        $fieldType = $options['fieldDefinitions']->getType();

        if (in_array($fieldType, array_keys($this->_fieldTypes))) {
            $fieldType = $this->_fieldTypes[$fieldType];
        }

        return $this->cakeView->Form->input($this->_getFieldName($table, $field, $options), [
            'type' => $fieldType,
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
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
     * Method responsible for converting csv field instance to database field instance.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array list of DbField instances
     */
    public function fieldToDb(CsvField $csvField)
    {
        $dbFields[] = new DbField(
            $csvField->getName(),
            static::DB_FIELD_TYPE,
            $csvField->getLimit(),
            $csvField->getRequired(),
            $csvField->getNonSearchable(),
            $csvField->getUnique()
        );

        return $dbFields;
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

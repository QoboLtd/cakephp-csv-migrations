<?php
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\TableRegistry;
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
     *
     * @var array
     */
    protected $_fieldTypes = [
        'text' => 'textarea',
        'blob' => 'textarea',
        'string' => 'text',
        'uuid' => 'text',
        'integer' => 'number',
        'decimal' => 'number',
        'url' => 'url',
        'email' => 'email',
        'phone' => 'tel',
    ];

    /**
     * Per type search operators.
     *
     * @var array
     */
    protected $_searchOperators = [
        'uuid' => ['is' => 'Is'],
        'related' => ['is' => 'Is'],
        'boolean' => ['is' => 'Is', 'is_not' => 'Is not'],
        'list' => ['is' => 'Is', 'is_not' => 'Is not'],
        'string' => [
            'contains' => 'Contains',
            'not_contains' => 'Does not contain',
            'starts_with' => 'Starts with',
            'ends_with' => 'Ends with'
        ],
        'text' => [
            'contains' => 'Contains',
            'not_contains' => 'Does not contain',
            'starts_with' => 'Starts with',
            'ends_with' => 'Ends with'
        ],
        'textarea' => [
            'contains' => 'Contains',
            'not_contains' => 'Does not contain',
            'starts_with' => 'Starts with',
            'ends_with' => 'Ends with'
        ],
        'email' => [
            'contains' => 'Contains',
            'not_contains' => 'Does not contain',
            'starts_with' => 'Starts with',
            'ends_with' => 'Ends with'
        ],
        'phone' => [
            'contains' => 'Contains',
            'not_contains' => 'Does not contain',
            'starts_with' => 'Starts with',
            'ends_with' => 'Ends with'
        ],
        'url' => [
            'contains' => 'Contains',
            'not_contains' => 'Does not contain',
            'starts_with' => 'Starts with',
            'ends_with' => 'Ends with'
        ],
        'integer' => ['is' => 'Is', 'is_not' => 'Is not', 'greater' => 'greater', 'less' => 'less'],
        'decimal' => ['is' => 'Is', 'is_not' => 'Is not', 'greater' => 'greater', 'less' => 'less'],
        'datetime' => ['is' => 'Is', 'is_not' => 'Is not', 'greater' => 'from', 'less' => 'to'],
        'date' => ['is' => 'Is', 'is_not' => 'Is not', 'greater' => 'from', 'less' => 'to'],
        'time' => ['is' => 'Is', 'is_not' => 'Is not', 'greater' => 'from', 'less' => 'to']
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct($cakeView = null)
    {
        if ($cakeView) {
            $this->cakeView = $cakeView;
        } else {
            $this->cakeView = new AppView();
        }
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function renderSearchInput($table, $field, array $options = [])
    {
        $fieldType = $options['fieldDefinitions']->getType();

        if (in_array($fieldType, array_keys($this->_fieldTypes))) {
            $fieldType = $this->_fieldTypes[$fieldType];
        }

        return $this->cakeView->Form->input('{{name}}', [
            'value' => '{{value}}',
            'type' => $fieldType,
            'label' => false
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = $data;

        return $result;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function getSearchOperators($table, $type)
    {
        if (empty($this->_searchOperators[$type])) {
            return [];
        }

        return $this->_searchOperators[$type];
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

        if (empty($table)) {
            return $field;
        }

        if (is_object($table)) {
            return $table->alias() . '.' . $field;
        }

        return $table . '.' . $field;
    }

    /**
     * Method that generates input label based on field name or optional options label parameter.
     * It can either return just the field label value or the html markup.
     *
     * @param  string  $field   Field name
     * @param  array   $options Field options
     * @param  bool    $html    Html flag
     * @return string           Label value or html markup
     */
    protected function _fieldToLabel($field, array $options = [], $html = true)
    {
        $result = array_key_exists('label', $options) ? (string)$options['label'] : $field;

        if (!$html || empty($result)) {
            return $result;
        }

        return $this->cakeView->Form->label($result);
    }

    /**
     * Returns arguments from database column definition.
     *
     * @param  \Cake\ORM\Table|string $table  Table instance or name
     * @param  string                 $column Column name
     * @param  array                  $args   Column arguments
     * @return array
     */
    protected function _getDbColumnArgs($table, $column, array $args = [])
    {
        $result = [];

        if (empty($table)) {
            return $result;
        }

        if (is_string($table)) {
            $table = TableRegistry::get($table);
        }

        $data = $table->schema()->column($column);

        if (empty($data)) {
            return $result;
        }

        if (empty($args)) {
            return $data;
        }

        foreach ($data as $k => $v) {
            if (!in_array($k, $args)) {
                continue;
            }

            $result[$k] = $v;
        }

        return $result;
    }
}

<?php
namespace CsvMigrations\FieldHandlers;

use Cake\Core\App;
use Cake\Network\Request;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DbField;
use CsvMigrations\FieldHandlers\FieldHandlerInterface;
use CsvMigrations\View\AppView;

/**
 * BaseFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to all field handlers.
 *
 * NOTE: Try to avoid inheriting from this class directly.
 *       Instead, use one of the more specific base classes.
 *
 * @abstract
 */
abstract class BaseFieldHandler implements FieldHandlerInterface
{
    /**
     * Default database field type
     */
    const DB_FIELD_TYPE = 'string';

    /**
     * Default HTML form field type
     */
    const INPUT_FIELD_TYPE = 'text';

    /**
     * Flag for rendering value as is
     */
    const RENDER_PLAIN_VALUE = 'plain';

    /**
     * Table object
     *
     * @var \Cake\ORM\Table
     */
    public $table;

    /**
     * Field name
     *
     * @var string
     */
    public $field;

    /**
     * View instance
     *
     * @var \Cake\View\View
     */
    public $cakeView;

    /**
     * Default options
     *
     * @var array
     */
    public $defaultOptions = [];

    /**
     * Custom form input templates.
     *
     * @var input
     */
    protected $_templates = [
        'input' => '<div class="input-group %s">
            <div class="input-group-addon">
                <i class="fa fa-%s"></i>
            </div>
            <input type="{{type}}" name="{{name}}"{{attrs}}/>
        </div>'
    ];

    /**
     * Constructor
     *
     * @param mixed  $table    Name or instance of the Table
     * @param string $field    Field name
     * @param object $cakeView Optional instance of the AppView
     */
    public function __construct($table, $field, $cakeView = null)
    {
        $this->setTable($table);
        $this->setField($field);
        $this->setDefaultOptions();
        $this->setView($cakeView);
    }

    /**
     * Set table
     *
     * @throws \InvalidArgumentException when table is empty
     * @param mixed $table Table name of instance
     * @return void
     */
    protected function setTable($table)
    {
        if (empty($table)) {
            throw new \InvalidArgumentException('Table cannot be empty.');
        }
        if (is_string($table)) {
            $this->table = TableRegistry::get($table);
        } else {
            $this->table = $table;
        }
    }

    /**
     * Set field
     *
     * @throws \InvalidArgumentException when field is empty
     * @param string $field Field name
     * @return void
     */
    protected function setField($field)
    {
        $field = (string)$field;
        if (empty($field)) {
            throw new \InvalidArgumentException('Field cannot be empty.');
        }
        $this->field = $field;
    }

    /**
     * Set default options
     *
     * Populate the $defaultOptions to make sure we always have
     * the fieldDefinitions options for the current field.
     *
     * @return void
     */
    protected function setDefaultOptions()
    {
        $stubFields = [
            $this->field => [
                'name' => $this->field,
                'type' => self::DB_FIELD_TYPE, // not static:: to preserve string
            ],
        ];
        if (method_exists($this->table, 'getFieldsDefinitions') && is_callable([$this->table, 'getFieldsDefinitions'])) {
            $fieldDefinitions = $this->table->getFieldsDefinitions($stubFields);
            $this->defaultOptions['fieldDefinitions'] = new CsvField($fieldDefinitions[$this->field]);
        } else {
            // This should never be the case, except, maybe
            // for some unit test runs or custom non-CSV
            // modules.
            $this->defaultOptions['fieldDefinitions'] = new CsvField($stubFields[$this->field]);
        }
    }

    /**
     * Fix provided options
     *
     * This method is here to fix some issues with backward
     * compatibility and make sure that $options parameters
     * are consistent throughout.
     *
     * @param array  $options Options to fix
     * @return array          Fixed options
     */
    protected function fixOptions(array $options = [])
    {
        $result = $options;
        if (empty($result)) {
            return $result;
        }

        // Previously, fieldDefinitions could be either an array or a CsvField instance.
        // Now we expect it to always be a CsvField instance.  So, if we have a non-empty
        // array, then instantiate CsvField with the values from it.
        if (!empty($result['fieldDefinitions']) && is_array($result['fieldDefinitions'])) {
            // Sometimes, when setting fieldDefinitions manually to render a particular
            // type, the name is omitted.  This works for an array, but doesn't work for
            // the CsvField instance, as the name is required.  Gladly, we know the name
            // and can fix it easily.
            if (empty($result['fieldDefinitions']['name'])) {
                $result['fieldDefinitions']['name'] = $this->field;
            }
            $result['fieldDefinitions'] = new CsvField($result['fieldDefinitions']);
        }

        return $result;
    }

    /**
     * Set view
     *
     * If an instance of the view is given, use that.
     * Otherwise, instantiate a new view.
     *
     * @param object $view View
     * @return void
     */
    protected function setView($view = null)
    {
        if ($view) {
            $this->cakeView = $view;
        } else {
            $this->cakeView = new AppView();
        }
    }

    /**
     * Render field input
     *
     * This method prepares the form input for the given field,
     * including the input itself, label, pre-populated value,
     * and so on.  The result can be controlled via the variety
     * of options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field input HTML
     */
    public function renderInput($data = '', array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $data = $this->_getFieldValueFromData($data);

        return $this->cakeView->Form->input($this->_getFieldName($options), [
            'type' => static::INPUT_FIELD_TYPE,
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
            'value' => $data
        ]);
    }

    /**
     * Render field search input
     *
     * This method prepares the search form input for the given field,
     * including the input itself, label, pre-populated value,
     * and so on.  The result can be controlled via the variety
     * of options.
     *
     * @param  array  $options Field options
     * @return array           Array of field input HTML, pre and post CSS, JS, etc
     */
    public function renderSearchInput(array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $content = $this->cakeView->Form->input('{{name}}', [
            'value' => '{{value}}',
            'type' => static::INPUT_FIELD_TYPE,
            'label' => false
        ]);

        return [
            'content' => $content
        ];
    }

    /**
     * Render field value
     *
     * This method prepares the output of the value for the given
     * field.  The result can be controlled via the variety of
     * options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field value
     */
    public function renderValue($data, array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $result = $this->_getFieldValueFromData($data);

        return $result;
    }

    /**
     * Convert CsvField to one or more DbField instances
     *
     * Simple fields from migrations CSV map one-to-one to
     * the database fields.  More complex fields can combine
     * multiple database fields for a single CSV entry.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array                                           DbField instances
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
     * Get search operators
     *
     * This method prepares a list of search operators that
     * are appropriate for a given field.
     *
     * @return array List of search operators
     */
    public function getSearchOperators()
    {
        return [
            'contains' => [
                'label' => 'contains',
                'operator' => 'LIKE',
                'pattern' => '%{{value}}%',
            ],
            'not_contains' => [
                'label' => 'does not contain',
                'operator' => 'NOT LIKE',
                'pattern' => '%{{value}}%',
            ],
            'starts_with' => [
                'label' => 'starts with',
                'operator' => 'LIKE',
                'pattern' => '{{value}}%',
            ],
            'ends_with' => [
                'label' => 'ends with',
                'operator' => 'LIKE',
                'pattern' => '%{{value}}',
            ],
        ];
    }

    /**
     * Get field label
     *
     * @todo Rename method to getLabel()
     * @param  string $field Optional field name
     * @return string Human-friendly field name
     */
    public function getSearchLabel($field = null)
    {
        if (empty($field)) {
            $field = $this->field;
        }

        return Inflector::humanize($field);
    }

    /**
     * Get field value from given data
     *
     * Extract field value from the variable, based on the type
     * of the variable.  Support types are:
     *
     * * Entity, use Entity property with the field name
     * * Request, use Request->data() with the key of the field name
     * * Otherwise assume the variable is the data already
     *
     * @param Entity|Request|mixed $data  Variable to extract value from
     * @param string               $field Optional field name
     * @return mixed
     */
    protected function _getFieldValueFromData($data, $field = null)
    {
        if (empty($field)) {
            $field = $this->field;
        }

        $result = $data;

        if ($data instanceof Entity) {
            $result = $data->$field;

            return $result;
        }

        if ($data instanceof Request) {
            $result = isset($data->data[$field]) ? $data->data[$field] : null;

            return $result;
        }

        return $result;
    }

    /**
     * Get field type by field handler class name.
     *
     * @param object $handler Field handler instance
     * @return string
     */
    protected function _getFieldTypeByFieldHandler($handler)
    {
        list(, $type) = pluginSplit(App::shortName(get_class($handler), 'FieldHandlers', 'FieldHandler'));

        return Inflector::underscore($type);
    }

    /**
     * Generate field name based on its options.
     *
     * @param  array  $options        Field options
     * @return string
     */
    protected function _getFieldName(array $options = [])
    {
        if (empty($this->table)) {
            return $this->field;
        }

        if (is_object($this->table)) {
            return $this->table->alias() . '.' . $this->field;
        }

        return $this->table . '.' . $this->field;
    }

    /**
     * Generates input label based on field name or label options
     *
     * It can either return just the field label value or the html markup.
     *
     * @param  array   $options Field options
     * @param  bool    $html    Html flag
     * @return string           Label value or html markup
     */
    protected function _fieldToLabel(array $options = [], $html = true)
    {
        $result = array_key_exists('label', $options) ? (string)$options['label'] : $this->field;

        if (!$html || empty($result)) {
            return $result;
        }

        return $this->cakeView->Form->label($result);
    }

    /**
     * Get database column definition.
     *
     * @param  array $args Column arguments
     * @return array
     */
    protected function _getDbColumnArgs(array $args = [])
    {
        $result = [];

        $data = [];
        try {
            $data = $this->table->schema()->column($this->field);
        } catch (\Exception $e) {
            // Ignore the fact that the database table does not exist
        }

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

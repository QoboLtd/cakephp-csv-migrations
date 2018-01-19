<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use InvalidArgumentException;
use RuntimeException;

class FieldHandlerFactory
{
    /**
     * Field Handler classes suffix
     */
    const HANDLER_SUFFIX = 'FieldHandler';

    /**
     * Field Handler Interface class name
     */
    const FIELD_HANDLER_INTERFACE = 'FieldHandlerInterface';

    /**
     * Loaded Table instances
     *
     * @var array
     */
    protected $_tableInstances = [];

    /**
     * View instance.
     *
     * @var \Cake\View\View
     */
    public $cakeView = null;

    /**
     * Constructor
     *
     * @param mixed $cakeView View object or null
     */
    public function __construct($cakeView = null)
    {
        $this->cakeView = $cakeView;
    }

    /**
     * Render field form input
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field input
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $table = $this->_getTableInstance($table);
        $handler = $this->_getHandler($table, $field, $options);

        return $handler->renderInput($data, $options);
    }

    /**
     * Render field form label
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  array  $options field options
     * @return string          field input
     */
    public function renderName($table, $field, array $options = [])
    {
        $table = $this->_getTableInstance($table);
        $handler = $this->_getHandler($table, $field, $options);

        //@TODO: add options for the renderName instance methods,
        //so we could customize the label.
        return $handler->renderName();
    }

    /**
     * Get search options
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  array  $options field options
     * @return array           Array of fields and their options
     */
    public function getSearchOptions($table, $field, array $options = [])
    {
        $table = $this->_getTableInstance($table);
        $handler = $this->_getHandler($table, $field, $options);

        return $handler->getSearchOptions($options);
    }

    /**
     * Render field value
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          list field value
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $table = $this->_getTableInstance($table);
        $handler = $this->_getHandler($table, $field, $options);

        return $handler->renderValue($data, $options);
    }

    /**
     * Convert field CSV into database fields
     *
     * **NOTE** For the time-being, we are not utilizing $table and $field
     *          parameters.  They are here to ease the near-future refactoring
     *          of the FieldHandlerFactory class into a proper (and simple)
     *          factory.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @param  mixed                                 $table    Name or instance of the Table
     * @param  string                                $field    Field name
     * @return array list of DbField instances
     */
    public function fieldToDb(CsvField $csvField, $table, $field = null)
    {
        if (!static::hasFieldHandler($csvField->getType())) {
            throw new RuntimeException("No field handler for type [" . $csvField->getType . "]");
        }
        $handlerName = static::_getHandlerClassName($csvField->getType(), true);

        return $handlerName::fieldToDb($csvField);
    }

    /**
     * Check if given field type has a field handler
     *
     * Previously, we used to load all available field handlers
     * via getList() method and check if the handler for the given
     * type was in that list.  However, this doesn't play well
     * with autoloaders.  It's better to rely on the autoloader
     * and namespaces, rather than on our search through directories.
     * Hence this check whether a particular handler exists.
     *
     * @param string $fieldType Field type
     * @return bool             True if yes, false otherwise
     */
    public static function hasFieldHandler($fieldType)
    {
        $interface = __NAMESPACE__ . '\\' . static::FIELD_HANDLER_INTERFACE;

        $handlerName = static::_getHandlerClassName($fieldType, true);
        if (class_exists($handlerName) && in_array($interface, class_implements($handlerName))) {
            return true;
        }

        return false;
    }

    /**
     * Get table instance
     *
     * @throws \InvalidArgumentException when $table is not an object or string
     * @param  mixed  $table  name or instance of the Table
     * @return object         Table instance
     */
    protected function _getTableInstance($table)
    {
        $tableName = '';

        if (is_object($table)) {
            $tableName = $table->alias();
            // Update instance cache with the freshest copy and exist
            $this->_tableInstances[$tableName] = $table;

            return $table;
        }

        // Avoid ambiguity
        if (!is_string($table)) {
            throw new InvalidArgumentException("Table must be a name or instance object");
        }

        // Return a cached instance if we have one
        if (in_array($table, array_keys($this->_tableInstances))) {
            return $this->_tableInstances[$table];
        }

        // Populate cache
        $this->_tableInstances[$table] = TableRegistry::get($table);

        return $this->_tableInstances[$table];
    }

    /**
     * Get field handler instance
     *
     * This method returns an instance of the appropriate
     * FieldHandler class.
     *
     * @throws \RuntimeException when failed to instantiate field handler
     * @param  Table         $table   Table instance
     * @param  string|array  $field   Field name
     * @param  array         $options Field options
     * @return object                 FieldHandler instance
     */
    protected function _getHandler(Table $table, $field, array $options = [])
    {
        if (empty($field)) {
            throw new InvalidArgumentException("Field parameter is empty");
        }

        // Save field name
        $fieldName = '';
        if (is_string($field)) {
            $fieldName = $field;
        }

        // Overwrite field with field difinitions options
        if (!empty($options['fieldDefinitions'])) {
            $field = $options['fieldDefinitions'];
        }

        // Prepare the stub field
        $stubFields = [];

        if (is_string($field)) {
            $stubFields = $this->getStubFromString($fieldName);
        }
        if (is_array($field)) {
            $stubFields = $this->getStubFromArray($fieldName, $field);
        }

        if (empty($stubFields)) {
            throw new InvalidArgumentException("Field can be either a string or an associative array");
        }

        $fieldDefinitions = $stubFields;
        if (method_exists($table, 'getFieldsDefinitions') && is_callable([$table, 'getFieldsDefinitions'])) {
            $fieldDefinitions = $table->getFieldsDefinitions($stubFields);
        }

        if (empty($fieldDefinitions[$fieldName])) {
            throw new RuntimeException("Failed to get definition for field '$fieldName'");
        }

        $field = new CsvField($fieldDefinitions[$fieldName]);
        $fieldType = $field->getType();

        $interface = __NAMESPACE__ . '\\' . static::FIELD_HANDLER_INTERFACE;

        $handlerName = static::_getHandlerClassName($fieldType, true);
        if (!class_exists($handlerName)) {
            throw new RuntimeException("Field handler class [$handlerName] for field type [$fieldType] does not exist");
        }
        if (!in_array($interface, class_implements($handlerName))) {
            throw new RuntimeException("Field handler class [$handlerName] does not implement interface [$interface]");
        }

        return new $handlerName($table, $fieldName, $this->cakeView);
    }

    /**
     * Get stub fields from a field name string
     *
     * @param string $fieldName Field name
     * @return array Stub fields
     */
    protected function getStubFromString($fieldName)
    {
        $result = [
            $fieldName => [
                'name' => $fieldName,
                'type' => 'string',
            ],
        ];

        return $result;
    }

    /**
     * Get stub fields from a field array
     *
     * @throws \InvalidArgumentException when field name or type are missing
     * @param string $fieldName Field name
     * @param array $field Field array
     * @return array Stub fields
     */
    protected function getStubFromArray($fieldName, array $field)
    {
        // Try our best to find the field name
        if (empty($field['name']) && !empty($fieldName)) {
            $field['name'] = $fieldName;
        }

        if (empty($field['name'])) {
            throw new InvalidArgumentException("Field array is missing 'name' key");
        }
        if (empty($field['type'])) {
            throw new InvalidArgumentException("Field array is missing 'type' key");
        }
        $fieldName = $field['name'];
        $result = [
            $fieldName => $field,
        ];

        return $result;
    }

    /**
     * Get field handler class name
     *
     * This method constructs handler class name based on provided field type.
     *
     * @param  string $type          field type
     * @param  bool   $withNamespace whether or not to include namespace
     * @return string                handler class name
     */
    protected static function _getHandlerClassName($type, $withNamespace = false)
    {
        $result = Inflector::camelize($type) . static::HANDLER_SUFFIX;

        if ($withNamespace) {
            $result = __NAMESPACE__ . '\\' . $result;
        }

        return $result;
    }
}

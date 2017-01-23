<?php
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\ForeignKeysHandler;
use DirectoryIterator;
use RegexIterator;

class FieldHandlerFactory
{
    /**
     * Default Field Handler class name
     */
    const DEFAULT_HANDLER_CLASS = 'Default';

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
     * Constructor method.
     *
     * @param mixed $cakeView View object or null
     */
    public function __construct($cakeView = null)
    {
        $this->cakeView = $cakeView;
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
        $table = $this->_getTableInstance($table);
        $options = $this->_getExtraOptions($table, $field, $options);
        $handler = $this->_getHandler($options['fieldDefinitions']->getType(), $table, $field);

        return $handler->renderInput($data, $options);
    }

    /**
     * Method responsible for rendering field's search input.
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @return string          field input
     */
    public function renderSearchInput($table, $field)
    {
        $table = $this->_getTableInstance($table);
        $options = $this->_getExtraOptions($table, $field);
        $handler = $this->_getHandler($options['fieldDefinitions']->getType(), $table, $field);

        return $handler->renderSearchInput($options);
    }

    /**
     * Method that returns field search operators based on field type.
     *
     * @param mixed $table Name or instance of the Table
     * @param string $field Field name
     * @return array
     */
    public function getSearchOperators($table, $field)
    {
        $table = $this->_getTableInstance($table);
        $options = $this->_getExtraOptions($table, $field);
        $type = $options['fieldDefinitions']->getType();
        $handler = $this->_getHandler($type, $table, $field);

        return $handler->getSearchOperators();
    }

    /**
     * Method that returns search field label.
     *
     * @param mixed $table Name or instance of the Table
     * @param string $field Field name
     * @return string
     */
    public function getSearchLabel($table, $field)
    {
        $table = $this->_getTableInstance($table);
        $options = $this->_getExtraOptions($table, $field);
        $handler = $this->_getHandler($options['fieldDefinitions']->getType(), $table, $field);

        return $handler->getSearchLabel();
    }

    /**
     * Method that renders specified field's value based on the field's type.
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
        $options = $this->_getExtraOptions($table, $field, $options);
        $handler = $this->_getHandler($options['fieldDefinitions']->getType(), $table, $field);

        return $handler->renderValue($data, $options);
    }

    /**
     * Method responsible for converting csv field instance to database field instance.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array list of DbField instances
     */
    public function fieldToDb(CsvField $csvField, $table, $field)
    {
        $handler = $this->_getHandler($csvField->getType(), $table, $field);
        $fields = $handler->fieldToDb($csvField, $table, $field);

        return $fields;
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
    public function hasFieldHandler($fieldType)
    {
        $interface = __NAMESPACE__ . '\\' . static::FIELD_HANDLER_INTERFACE;

        $handlerName = $this->_getHandlerByFieldType($fieldType, true);
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
        } elseif (is_string($table)) {
            // Will need to do some work later
            $tableName = $table;
        } else {
            // Avoid ambiguity
            throw new \InvalidArgumentException("Table must be a name or instance object");
        }

        // Return a cached instance if we have one
        if (in_array($tableName, array_keys($this->_tableInstances))) {

            return $this->_tableInstances[$tableName];
        }

        // Populate cache
        $this->_tableInstances[$tableName] = TableRegistry::get($tableName);

        return $this->_tableInstances[$tableName];
    }

    /**
     * Method that adds extra parameters to the field options array.
     *
     * @param  object $tableInstance instance of the Table
     * @param  string $field         field name
     * @param  array  $options       field options
     * @return array
     */
    protected function _getExtraOptions($tableInstance, $field, array $options = [])
    {
        // get fields definitions
        // if the field is csv-based
        if (is_callable([$tableInstance, 'getFieldsDefinitions']) && method_exists($tableInstance, 'getFieldsDefinitions')) {
            $fieldsDefinitions = $tableInstance->getFieldsDefinitions($tableInstance->alias());
        }

        /*
        add field definitions to options array as CsvField Instance
         */
        if (!empty($fieldsDefinitions[$field])) {
            $options['fieldDefinitions'] = new CsvField($fieldsDefinitions[$field]);
        } else {
            /*
             * @todo make this better, probably define defaults (scenario virtual fields)
             */
            if (empty($options['fieldDefinitions']['type'])) {
                $options['fieldDefinitions']['type'] = 'string';
            }
            $options['fieldDefinitions']['name'] = $field;

            $options['fieldDefinitions'] = new CsvField($options['fieldDefinitions']);
        }

        return $options;
    }

    /**
     * Get field handler instance
     *
     * This method returns an instance of the appropriate
     * FieldHandler class based on field Type.
     *
     * In case the field handler cannot be found or instantiated
     * the method either returns a default handler, or throws an
     * expcetion (based on $failOnError parameter).
     *
     * @throws \RuntimeException when failed to instantiate field handler and $failOnError is true
     * @param  string  $fieldType field type
     * @param  bool   $failOnError Whether or not to throw exception on failure
     * @return object            FieldHandler instance
     */
    protected function _getHandler($fieldType, $table, $field, $failOnError = false)
    {
        $interface = __NAMESPACE__ . '\\' . static::FIELD_HANDLER_INTERFACE;

        $handlerName = $this->_getHandlerByFieldType($fieldType, true);
        if (class_exists($handlerName) && in_array($interface, class_implements($handlerName))) {
            return new $handlerName($table, $field, $this->cakeView);
        }

        // Field hanlder does not exist, throw exception if necessary
        if ($failOnError) {
            throw new \RuntimeException("No field handler defined for field type [$fieldType]");
        }

        // Use default field handler
        $handlerName = __NAMESPACE__ . '\\' . static::DEFAULT_HANDLER_CLASS . static::HANDLER_SUFFIX;
        if (class_exists($handlerName) && in_array($interface, class_implements($handlerName))) {
            return new $handlerName($table, $field, $this->cakeView);
        }

        // Neither the handler, nor the default handler can be used
        throw new \RuntimeException("Default field handler [" . static::DEFAULT_HANDLER_CLASS . "] cannot be used");
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
    protected function _getHandlerByFieldType($type, $withNamespace = false)
    {
        $result = Inflector::camelize($type) . static::HANDLER_SUFFIX;

        if ($withNamespace) {
            $result = __NAMESPACE__ . '\\' . $result;
        }

        return $result;
    }
}

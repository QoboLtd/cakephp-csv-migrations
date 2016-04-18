<?php
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\ForeignKeysHandler;

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
     * Current Table name
     *
     * @var string
     */
    protected $_tableName;

    /**
     * Loaded Table instances
     *
     * @var array
     */
    protected $_tableInstances = [];

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
        $handler = $this->_getHandler($options['fieldDefinitions']['type']);

        return $handler->renderInput($table, $field, $data, $options);
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
        $handler = $this->_getHandler($options['fieldDefinitions']['type']);

        return $handler->renderValue($table, $field, $data, $options);
    }

    /**
     * Method that sets and returns Table instance
     *
     * @param  mixed  $table  name or instance of the Table
     * @return object         Table instance
     */
    protected function _getTableInstance($table)
    {
        // set table name
        if (is_object($table)) {
            $this->setTableName($table->alias());
        } else {
            $this->setTableName($table);
        }

        $tableInstance = $this->_setTableInstance($table);

        return $tableInstance;
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
        $fieldsDefinitions = $tableInstance->getFieldsDefinitions();

        /**
         * @todo make this better, probably define defaults (scenario virtual fields)
         */
        $options['fieldDefinitions']['type'] = 'string';
        if (!empty($fieldsDefinitions[$field])) {
            // add field definitions to options array
            $options['fieldDefinitions'] = $fieldsDefinitions[$field];
        }

        return $options;
    }

    /**
     * Method that returns an instance of the appropriate
     * FieldHandler class based on field Type.
     *
     * @param  array  $fieldType field type
     * @return object            FieldHandler instance
     */
    protected function _getHandler($fieldType)
    {
        // get appropriate field handler
        $handlerName = $this->_getHandlerByFieldType($fieldType, true);

        $interface = __NAMESPACE__ . '\\' . static::FIELD_HANDLER_INTERFACE;
        if (class_exists($handlerName) && in_array($interface, class_implements($handlerName))) {
            //
        } else { // switch to default field handler
            $handlerName = __NAMESPACE__ . '\\' . static::DEFAULT_HANDLER_CLASS . static::HANDLER_SUFFIX;
        }

        return new $handlerName;
    }

    /**
     * Set table name
     *
     * @param string $tableName table name
     * @return void
     */
    public function setTableName($tableName)
    {
        $this->_tableName = $tableName;
    }

    /**
     * Method that retrieves handler class name based on provided field type.
     * It also handles more advanced field types like foreign key and list fields.
     * Example: if field type is 'string' then 'StringFieldHandler' will be returned.
     * Example: if field type is 'related:users' then 'RelatedFieldHandler' will be returned.
     *
     * @param  string $type field type
     * @param  bool   $fqcn true to use fully-qualified class name
     * @return string       handler class name
     */
    protected function _getHandlerByFieldType($type, $fqcn = false)
    {
        if (false !== $pos = strpos($type, ':')) {
            $type = substr($type, 0, $pos);
        }

        $result = Inflector::classify($type) . static::HANDLER_SUFFIX;

        if ($fqcn) {
            $result = __NAMESPACE__ . '\\' . $result;
        }

        return $result;
    }

    /**
     * Method that adds specified table to the _tableInstances
     * array and returns the table's instance.
     *
     * @param  mixed $table name or instance of the Table
     * @return object       instance of specified Table
     */
    protected function _setTableInstance($table)
    {
        // add table instance to _modelInstances array
        if (!in_array($this->_tableName, array_keys($this->_tableInstances))) {
            // get table instance
            if (!is_object($table)) {
                $table = TableRegistry::get($this->_tableName);
            }
            $this->_tableInstances[$this->_tableName] = $table;
        }

        return $this->_tableInstances[$this->_tableName];
    }
}

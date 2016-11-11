<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\CsvField;

interface FieldHandlerInterface
{
    /**
     * Constructor method.
     *
     * @param mixed $cakeView View object or null
     */
    public function __construct($cakeView = null);

    /**
     * Method responsible for rendering field's input.
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field input
     */
    public function renderInput($table, $field, $data = '', array $options = []);

    /**
     * Method responsible for rendering field's value.
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field value
     */
    public function renderValue($table, $field, $data, array $options = []);

    /**
     * Method responsible for converting csv field instance to database field instance.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array                                           DbField instances
     */
    public function fieldToDb(CsvField $csvField);
}

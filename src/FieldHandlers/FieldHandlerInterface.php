<?php
namespace CsvMigrations\FieldHandlers;

interface FieldHandlerInterface
{
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
     * Method responsible for converting field for migration.
     *
     * @param  string $field field name
     * @return array         converted field
     */
    public function convertField($field);
}

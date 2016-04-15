<?php
namespace CsvMigrations\FieldHandlers;

interface FieldHandlerInterface
{
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
    public function renderInput($plugin, $table, $field, $data = '', array $options = []);

    /**
     * Method responsible for rendering field's value.
     *
     * @param  string $plugin  plugin name
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field value
     */
    public function renderValue($plugin, $table, $field, $data, array $options = []);
}

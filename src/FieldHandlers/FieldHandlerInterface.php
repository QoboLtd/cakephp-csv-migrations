<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\CsvField;

/**
 * FieldHandlerInterface
 *
 * FieldHandlerInterface defines the contract that all
 * field handler classes have to implement.
 */
interface FieldHandlerInterface
{
    /**
     * Constructor
     *
     * @param  mixed  $table   Name or instance of the Table
     * @param  string $field   Field name
     * @param object $cakeView Optional instance of the AppView
     */
    public function __construct($table, $field, $cakeView = null);

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
    public function renderInput($data = '', array $options = []);

    /**
     * Render field search input
     *
     * This method prepares the search form input for the given field,
     * including the input itself, label, pre-populated value,
     * and so on.  The result can be controlled via the variety
     * of options.
     *
     * @param array  $options Field options
     * @return array          Array of field input HTML, pre and post CSS, JS, etc
     */
    public function renderSearchInput(array $options = []);

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
    public function renderValue($data, array $options = []);

    /**
     * Get search operators
     *
     * This method prepares a list of search operators that
     * are appropriate for a given field.
     *
     * @todo Drop the $type parameter, as field handler should know this already
     * @param string $type  Field type
     * @return array        List of search operators
     */
    public function getSearchOperators($type);

    /**
     * Get field label
     *
     * @todo Rename method to getLabel()
     * @return string        Human-friendly field name
     */
    public function getSearchLabel();

    /**
     * Convert CsvField to one or more DbField instances
     *
     * Simple fields from migrations CSV map one-to-one to
     * the database fields.  More complex fields can combine
     * multiple database fields for a single CSV entry.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @param  mixed  $table Name or instance of the Table
     * @param  string $field Field name
     * @return array                                           DbField instances
     */
    public function fieldToDb(CsvField $csvField, $table, $field);
}

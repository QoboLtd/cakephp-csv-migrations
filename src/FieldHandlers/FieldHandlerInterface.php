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

use CsvMigrations\FieldHandlers\Config\ConfigInterface;

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
     * @param \CsvMigrations\FieldHandlers\Config\ConfigInterface $config Instance of field handler config
     */
    public function __construct(ConfigInterface $config);

    /**
     * Config instance getter
     *
     * @return \CsvMigrations\FieldHandlers\Config\ConfigInterface
     */
    public function getConfig();

    /**
     * Config instance setter
     *
     * @param \CsvMigrations\FieldHandlers\Config\ConfigInterface $config Instance of field handler config
     * @return void
     */
    public function setConfig(ConfigInterface $config);

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
     * Get options for field search
     *
     * This method prepares an array of search options, which includes
     * label, form input, supported search operators, etc.  The result
     * can be controlled with a variety of options.
     *
     * @param  array  $options Field options
     * @return array           Array of field input HTML, pre and post CSS, JS, etc
     */
    public function getSearchOptions(array $options = []);

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
     * Render field name
     *
     * @return string
     */
    public function renderName();

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
    public static function fieldToDb(CsvField $csvField);
}

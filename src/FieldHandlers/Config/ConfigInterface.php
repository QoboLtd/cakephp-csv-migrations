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
namespace CsvMigrations\FieldHandlers\Config;

/**
 * ConfigInterface
 *
 * ConfigInterface defines the contract
 * for configuration of the field handler
 */
interface ConfigInterface
{
    /**
     * Constructor
     *
     * @param string $field Field name
     * @param mixed $table Table name or instance
     * @param array $options Options
     */
    public function __construct($field, $table = null, array $options = []);

    /**
     * Set field
     *
     * @throws \InvalidArgumentException when field is empty or not a string
     * @param string $field Field name
     * @return void
     */
    public function setField($field);

    /**
     * Get field
     *
     * @return string
     */
    public function getField();

    /**
     * Set table
     *
     * @param mixed $table Table name or instance
     * @return void
     */
    public function setTable($table = null);

    /**
     * Get table
     *
     * @return object
     */
    public function getTable();

    /**
     * Set options
     *
     * @param array $options Options
     * @return void
     */
    public function setOptions(array $options = []);

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions();

    /**
     * Set config
     *
     * @throws \InvalidArgumentException for invalid configuration
     * @param array $config Field Handler configuration
     * @return void
     */
    public function setConfig(array $config);

    /**
     * Get config
     *
     * @throws \InvalidArgumentException for invalid configuration
     * @return array
     */
    public function getConfig();

    /**
     * Validate config
     *
     * @throws \InvalidArgumentException for invalid configuration
     * @param array $config Field Handler configuration
     * @return void
     */
    public function validateConfig(array $config);
}

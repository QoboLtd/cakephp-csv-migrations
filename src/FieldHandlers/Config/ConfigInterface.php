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

use Cake\View\View;

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
     * Set view
     *
     * @param \Cake\View\View $view View instance
     * @return void
     */
    public function setView(View $view);

    /**
     * Get view
     *
     * @return \Cake\View\View
     */
    public function getView();

    /**
     * Set providers
     *
     * @throws \InvalidArgumentException for invalid providers
     * @param array $providers List of provider names and classes
     * @return void
     */
    public function setProviders(array $providers);

    /**
     * Get providers
     *
     * @throws \InvalidArgumentException for invalid providers
     * @return array
     */
    public function getProviders();

    /**
     * Get provider by name
     *
     * @throws \InvalidArgumentException for invalid provider
     * @param string $name Name of the provider to get
     * @return array
     */
    public function getProvider($name);

    /**
     * Validate providers
     *
     * @throws \InvalidArgumentException for invalid providers
     * @param array $providers List of provider names and classes
     * @return void
     */
    public function validateProviders(array $providers);
}

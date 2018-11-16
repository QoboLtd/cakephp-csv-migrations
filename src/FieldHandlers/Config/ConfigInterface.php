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

use Cake\Datasource\RepositoryInterface;
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
    public function __construct(string $field, $table = null, array $options = []);

    /**
     * Set field
     *
     * @throws \InvalidArgumentException when field is empty or not a string
     * @param string $field Field name
     * @return void
     */
    public function setField(string $field) : void;

    /**
     * Get field
     *
     * @return string
     */
    public function getField() : string;

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
     * @return \Cake\Datasource\RepositoryInterface
     */
    public function getTable() : RepositoryInterface;

    /**
     * Set options
     *
     * @param array $options Options
     * @return void
     */
    public function setOptions(array $options = []) : void;

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions() : array;

    /**
     * Set view
     *
     * @param \Cake\View\View $view View instance
     * @return void
     */
    public function setView(View $view) : void;

    /**
     * Get view
     *
     * @return \Cake\View\View
     */
    public function getView() : View;

    /**
     * Set providers
     *
     * @throws \InvalidArgumentException for invalid providers
     * @param array $providers List of provider names and classes
     * @return void
     */
    public function setProviders(array $providers) : void;

    /**
     * Get providers
     *
     * @throws \InvalidArgumentException for invalid providers
     * @return array
     */
    public function getProviders() : array;

    /**
     * Get provider by name
     *
     * @throws \InvalidArgumentException for invalid provider
     * @param string $name Name of the provider to get
     * @return string
     */
    public function getProvider(string $name) : string;

    /**
     * Validate providers
     *
     * @throws \InvalidArgumentException for invalid providers
     * @param array $providers List of provider names and classes
     * @return void
     */
    public function validateProviders(array $providers) : void;
}

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
namespace CsvMigrations\FieldHandlers\Provider\Config;

/**
 * ConfigInterface
 *
 * ConfigInterface defines the contract
 * for configuration of the field handler
 */
interface ConfigInterface
{
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

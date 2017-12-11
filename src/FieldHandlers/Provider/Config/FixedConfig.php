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

use InvalidArgumentException;

/**
 * FixedConfig
 *
 * This class provides the functionality of the
 * pre-defined field handler configurations.
 */
abstract class FixedConfig extends Config
{
    /**
     * Set config
     *
     * @throws \InvalidArgumentException for invalid configuration
     * @param array $config Field Handler configuration
     * @return void
     */
    public function setConfig(array $config)
    {
        throw new InvalidArgumentException("Setting predefined configuration is not allowed");
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig()
    {
        $this->validateConfig($this->config);

        return $this->config;
    }
}

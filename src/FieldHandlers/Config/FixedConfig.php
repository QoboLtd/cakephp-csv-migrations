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
     * Set providers
     *
     * @throws \InvalidArgumentException for invalid providers
     * @param array $providers List of provider names and classes
     * @return void
     */
    public function setProviders(array $providers)
    {
        throw new InvalidArgumentException("Setting predefined providers is not allowed");
    }
}

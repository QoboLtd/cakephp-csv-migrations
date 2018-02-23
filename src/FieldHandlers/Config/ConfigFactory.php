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

use RuntimeException;

/**
 * ConfigFactory
 *
 * This class provides an easy way to get an
 * instance of the field handler configuration.
 */
class ConfigFactory
{
    /**
     * Get configuration instance by type
     *
     * @throws \InvalidArgumentException for unsupported configuration types
     * @param string $type Configuration type (e.g.: string, email, uuid)
     * @param string $field Field name
     * @param mixed $table Table name or instance
     * @param array $options Configuration options
     * @return \CsvMigrations\FieldHandlers\Config\ConfigInterface
     */
    public static function getByType($type, $field, $table = null, array $options = [])
    {
        $configClass = __NAMESPACE__ . '\\' . ucfirst($type) . 'Config';
        if (!class_exists($configClass)) {
            throw new RuntimeException("Configuration type [$type] is not supported");
        }

        $result = new $configClass($field, $table, $options);

        return $result;
    }
}
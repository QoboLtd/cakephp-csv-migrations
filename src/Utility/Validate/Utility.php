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
namespace CsvMigrations\Utility\Validate;

use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use Exception;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility as QoboUtility;

/**
 * Utility Class
 *
 * This class provices utility methods mostly
 * useful for validation of the system setup
 * and configuration files.
 */
class Utility
{
    /**
     * Get the list of all modules
     *
     * @return array
     */
    public static function getModules()
    {
        $path = Configure::read('CsvMigrations.modules.path');
        $result = QoboUtility::findDirs($path);

        return $result;
    }

    /**
     * Check if the given module is valid
     *
     * @param string $module Module name to check
     * @return bool True if module is valid, false otherwise
     */
    public static function isValidModule($module)
    {
        $result = false;

        $modules = static::getModules();
        if (in_array($module, $modules)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Check if the given list is valid
     *
     * Lists with no items are assumed to be
     * invalid.
     *
     * @param string $list List name to check
     * @return bool True if valid, false is otherwise
     */
    public static function isValidList($list)
    {
        $result = false;

        $module = null;
        if (strpos($list, '.') !== false) {
            list($module, $list) = explode('.', $list, 2);
        }
        $listItems = [];
        try {
            $mc = new ModuleConfig(ConfigType::LISTS(), $module, $list, ['cacheSkip' => true]);
            $listItems = $mc->parse()->items;
        } catch (Exception $e) {
            // We don't care about the specifics of the failure
        }

        if ($listItems) {
            $result = true;
        }

        return $result;
    }

    /**
     * Check if the field is defined in the module migration
     *
     * If the migration file does not exist or is not
     * parseable, it is assumed the field is real.  Presence
     * and validity of the migration file is checked
     * elsewhere.
     *
     * @param string $module Module to check in
     * @param string $field Field to check
     * @return bool True if field is real, false otherwise
     */
    public static function isRealModuleField($module, $field)
    {
        $result = false;

        $moduleFields = [];
        try {
            $mc = new ModuleConfig(ConfigType::MIGRATION(), $module, null, ['cacheSkip' => true]);
            $moduleFields = json_decode(json_encode($mc->parse()), true);
        } catch (Exception $e) {
            // We already report issues with migration in _checkMigrationPresence()
        }

        // If we couldn't get the migration, we cannot verify if the
        // field is real or not.  To avoid unnecessary fails, we
        // assume that it's real.
        if (empty($moduleFields)) {
            return true;
        }

        foreach ($moduleFields as $moduleField) {
            if ($field == $moduleField['name']) {
                return true;
            }
        }

        return $result;
    }

    /**
     * Check if the field is defined in the module's virtual fields
     *
     * The validity of the virtual field definition is checked
     * elsewhere.  Here we only verify that the field exists in
     * the `[virtualFields]` section definition.
     *
     * @param string $module Module to check in
     * @param string $field Field to check
     * @return bool True if field is real, false otherwise
     */
    public static function isVirtualModuleField($module, $field)
    {
        $result = false;

        $config = [];
        try {
            $mc = new ModuleConfig(ConfigType::MODULE(), $module, null, ['cacheSkip' => true]);
            $config = (array)json_decode(json_encode($mc->parse()), true);
        } catch (Exception $e) {
            return $result;
        }

        if (empty($config)) {
            return $result;
        }

        if (empty($config['virtualFields'])) {
            return $result;
        }

        if (!is_array($config['virtualFields'])) {
            return $result;
        }

        foreach ($config['virtualFields'] as $virtualField => $realFields) {
            if ($virtualField == $field) {
                return true;
            }
        }

        return $result;
    }

    /**
     * Check if the given field is valid for given module
     *
     * If valid fields are not available from the migration
     * we will assume that the field is valid.
     *
     * @param string $module Module to check in
     * @param string $field Field to check
     * @return bool True if field is valid, false otherwise
     */
    public static function isValidModuleField($module, $field)
    {
        $result = false;

        if (static::isRealModuleField($module, $field) || static::isVirtualModuleField($module, $field)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Check if the field type is valid
     *
     * Migration field type needs a field handler configuration.
     *
     * @param string $type Field type
     * @return bool True if valid, false otherwise
     */
    public static function isValidFieldType($type)
    {
        $result = false;

        try {
            $config = ConfigFactory::getByType($type, 'dummy_field');
        } catch (Exception $e) {
            return $result;
        }

        $result = true;

        return $result;
    }
}

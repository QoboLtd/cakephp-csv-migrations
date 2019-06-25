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
use Cake\Utility\Hash;
use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\ModuleConfig\Parser\Parser;
use Qobo\Utils\Utility as QoboUtility;
use Qobo\Utils\Utility\Convert;

/**
 * Utility Class
 *
 * This class provices utility methods mostly
 * useful for validation of the system setup
 * and configuration files.
 */
class Utility
{
    private static $cache = [];

    /**
     * Get the list of all modules
     *
     * @return string[]
     */
    public static function getModules() : array
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
    public static function isValidModule(string $module) : bool
    {
        return in_array($module, static::getModules());
    }

    /**
     * Check if the given list is valid
     *
     * Lists with no items are assumed to be invalid.
     *
     * @param string $list List name to check
     * @param string $module Module name to check the list in
     * @return bool True if valid, false otherwise
     */
    public static function isValidList(string $list, string $module = '') : bool
    {
        if (strpos($list, '.') !== false) {
            list($module, $list) = explode('.', $list, 2);
        }

        $listItems = null;
        try {
            $mc = new ModuleConfig(ConfigType::LISTS(), $module, $list, ['cacheSkip' => true]);
            $config = $mc->parse();
            $listItems = property_exists($config, 'items') ? $config->items : null;
        } catch (InvalidArgumentException $e) {
            return false;
        }

        if (null === $listItems) {
            return false;
        }

        return true;
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
    public static function isRealModuleField(string $module, string $field) : bool
    {
        $fields = self::getRealModuleFields($module);

        // If we couldn't get the migration, we cannot verify if the
        // field is real or not.  To avoid unnecessary fails, we
        // assume that it's real.
        if (empty($fields)) {
            return true;
        }

        return in_array($field, $fields);
    }

    /**
     * Returns a list of fields defined in `migration.json`.
     *
     * @param string $module Module name.
     * @param bool $validate Should the data be validated against the schema.
     * @return string[] List of fields.
     */
    public static function getRealModuleFields(string $module, bool $validate = true) : array
    {
        $moduleFields = [];

        $cacheKey = implode('.', [ConfigType::MIGRATION(), $module, null]);
        if (array_key_exists($cacheKey, self::$cache)) {
            return self::$cache[$cacheKey];
        }

        $mc = new ModuleConfig(ConfigType::MIGRATION(), $module, null, ['cacheSkip' => true]);
        $mc->setParser(new Parser($mc->createSchema(), ['validate' => $validate]));
        $moduleFields = Convert::objectToArray($mc->parse());
        self::$cache[$cacheKey] = (array)Hash::extract($moduleFields, '{*}.name');

        return self::$cache[$cacheKey];
    }

    /**
     * Returns a list of virtual fields in `config.json`.
     *
     * @param string $module Module name.
     * @param bool $validate Should the data be validated against the schema.
     * @return string[] list of virtual fields.
     */
    public static function getVirtualModuleFields(string $module, bool $validate = true) : array
    {
        $fields = [];

        $cacheKey = implode('.', [ConfigType::MODULE(), $module, null]);
        if (!array_key_exists($cacheKey, self::$cache)) {
            $mc = new ModuleConfig(ConfigType::MODULE(), $module, null, ['cacheSkip' => true]);
            $mc->setParser(new Parser($mc->createSchema(), ['validate' => $validate]));
            self::$cache[$cacheKey] = Convert::objectToArray($mc->parse());
        }

        $virtualFields = self::$cache[$cacheKey];
        if (isset($virtualFields['virtualFields'])) {
            $fields = $virtualFields['virtualFields'];
        }

        return $fields;
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
    public static function isVirtualModuleField(string $module, string $field) : bool
    {
        $config = self::getVirtualModuleFields($module);

        if (empty($config) || !is_array($config)) {
            return false;
        }

        return in_array($field, array_keys($config));
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
    public static function isValidModuleField(string $module, string $field) : bool
    {
        return static::isRealModuleField($module, $field) || static::isVirtualModuleField($module, $field) || static::isRealRelationField($module, $field);
    }

    /**
     * Check if the field type is valid
     *
     * Migration field type needs a field handler configuration.
     *
     * @param string $type Field type
     * @return bool True if valid, false otherwise
     */
    public static function isValidFieldType(string $type) : bool
    {
        try {
            $config = ConfigFactory::getByType($type, 'dummy_field');
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return true;
    }
}

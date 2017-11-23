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
namespace CsvMigrations\Utility;

use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Exception;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;

class Validate
{
    /**
     * Get a list of CSV modules
     *
     * @return array
     */
    public static function getModules()
    {
        $path = Configure::read('CsvMigrations.modules.path');
        $result = Utility::findDirs($path);

        return $result;
    }

    /**
     * Check if the given module is valid
     *
     * @param string $module Module name
     * @return bool True if valid, false otherwise
     */
    public static function isValidModule($module)
    {
        $result = false;

        $module = trim($module);
        if (empty($module)) {
            return $result;
        }

        $modules = static::getModules();
        if (empty($modules)) {
            return $result;
        }

        $result = in_array($module, $modules);

        return $result;
    }

    /**
     * Check if the given list is valid
     *
     * List name can be either plain, like 'genders',
     * or include a module name, like 'Leads.sources'.
     * If module is unknown, null is assumed.  Lists
     * with no values are assumed invalid.
     *
     * @param string $list List name
     * @return bool True if valid, false otherwise
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
            $mc = new ModuleConfig(ConfigType::LISTS(), $module, $list);
            $listItems = $mc->parse()->items;
        } catch (Exception $e) {
            return $result;
        }

        if (!empty($listItems)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Check if the given field is valid for given module
     *
     * Valid fields are either real or virtual
     *
     * @param string $module Module name to check in
     * @param string $field Field to check
     * @return bool True if valid, false otherwise
     */
    public static function isValidModuleField($module, $field)
    {
        $result = false;

        if (static::isRealModuleField($module, $field)) {
            $result = true;
        }

        if (static::isVirtualModuleField($module, $field)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Check if the field is defined in the module migration config
     *
     * If the migration file does not exist or is not
     * parseable, we don't have a way to verify, so we
     * assume the field is real.  Only if the config
     * exists, is parseable, and the given field is not
     * defined in it, we return false.
     *
     * @param string $module Module name to check in
     * @param string $field Field name to check in
     * @return bool True if field is real, false if not
     */
    public static function isRealModuleField($module, $field)
    {
        $result = true;

        $moduleFields = [];
        try {
            $mc = new ModuleConfig(ConfigType::MIGRATION(), $module);
            $moduleFields = json_decode(json_encode($mc->parse()), true);
        } catch (Exception $e) {
            return $result;
        }

        if (empty($moduleFields)) {
            return $result;
        }

        foreach ($moduleFields as $moduleField) {
            if ($field == $moduleField['name']) {
                return $result;
            }
        }

        $result = false;

        return $result;
    }

    /**
     * Check if the field is defined in the module virtual fields config
     *
     * Here we only check if the field is defined in the
     * module's `[virtualFields]` configuration.  Not
     * whether it is a proper definition or not.
     *
     * @param string $module Module name to check in
     * @param string $field Field name to check in
     * @return bool True if field is defined as virtual, false otherwise
     */
    public static function isVirtualModuleField($module, $field)
    {
        $result = false;

        $config = [];
        try {
            $mc = new ModuleConfig(ConfigType::MODULE(), $module);
            $config = json_decode(json_encode($mc->parse()), true);
        } catch (Exception $e) {
            return $result;
        }

        if (empty($config) || empty($config['virtualFields'])) {
            return $result;
        }

        $result = in_array($field, array_keys($config['virtualFields']));

        return $result;
    }

    /**
     * Check if the field type is valid
     *
     * Migration field type needs a field handler.
     *
     * @param string $type Field type
     * @return bool True if valid, false otherwise
     */
    public static function isValidFieldType($type)
    {
        $result = false;

        $fhf = new FieldHandlerFactory();
        if ($fhf->hasFieldHandler($type)) {
            $result = true;
        }

        return $result;
    }
}

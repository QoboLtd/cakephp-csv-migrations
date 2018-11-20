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
use CsvMigrations\Utility\Validate\Check\CheckInterface;
use RuntimeException;

class Check
{
    /**
     * @var string $interface Interface that all Check classes must implement
     */
    protected static $interface = CheckInterface::class;

    /**
     * Get an instance of a given check class
     *
     * @throws \RuntimeException when class does not exist or is invalid
     * @param string $checkClass Name of the check class
     * @return \CsvMigrations\Utility\Validate\Check\CheckInterface
     */
    public static function getInstance(string $checkClass) : CheckInterface
    {
        $checkClass = $checkClass;

        if (!class_exists($checkClass)) {
            throw new RuntimeException("Check class [$checkClass] does not exist");
        }

        if (!in_array(static::$interface, array_keys(class_implements($checkClass)))) {
            throw new RuntimeException("Check class [$checkClass] does not implement [" . static::$interface . "]");
        }

        return new $checkClass();
    }

    /**
     * Get ValidateShell checks configuration for a given module
     *
     * If no checks configured for the given module, return
     * the default checks instead (aka checks for module
     * '_default').
     *
     * @param string $module Module name
     * @return mixed[]
     */
    public static function getList(string $module) : array
    {
        $default = Configure::read('CsvMigrations.ValidateShell.module._default');
        $result = Configure::read('CsvMigrations.ValidateShell.module.' . $module);
        $result = empty($result) ? $default : $result;
        $result = empty($result['checks']) ? [] : $result['checks'];

        return $result;
    }
}

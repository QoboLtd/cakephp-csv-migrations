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

use CsvMigrations\Utility\Validate;
use InvalidArgumentException;
use RuntimeException;

class ValidModuleCheck extends BaseCheck
{
    /**
     * Check if the given module is valid
     *
     * @throws \InvalidArgumentException when data is empty or incorrect
     * @throws \RuntimeException when data does not pass the check
     * @param array $data Data to check
     * @return bool Always true
     */
    public static function isOk(array $data)
    {
        if (empty($data['module'])) {
            throw new InvalidArgumentException("'module' parameter is not specified");
        }

        $module = trim($data['module']);

        if (empty($module)) {
            throw new InvalidArgumentException("'module' parameter is empty");
        }

        $modules = Validate::getModules();
        if (empty($modules)) {
            throw new RuntimeException("Cannot find module '$module' in the list of empty modules");
        }

        if (!in_array($module, $modules)) {
            throw new RuntimeException("Module '$module' not found in the list of valid modules");
        }

        return true;
    }
}

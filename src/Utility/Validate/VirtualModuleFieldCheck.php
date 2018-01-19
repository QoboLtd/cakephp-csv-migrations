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
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use RuntimeException;

class VirtualModuleFieldCheck extends BaseCheck
{
    /**
     * Check if the field is defined in the module virtual fields config
     *
     * Here we only check if the field is defined in the module's
     * `[virtualFields]` configuration.  Not whether it is a proper
     * definition or not.
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

        if (empty($data['field'])) {
            throw new InvalidArgumentException("'field' parameter is not specified");
        }

        $module = trim($data['module']);
        if (empty($module)) {
            throw new InvalidArgumentException("'module' parameter is empty");
        }

        $field = trim($data['field']);
        if (empty($field)) {
            throw new InvalidArgumentException("'field' parameter is empty");
        }

        $config = [];
        try {
            $mc = new ModuleConfig(ConfigType::MODULE(), $module);
            $config = json_decode(json_encode($mc->parse()), true);
        } catch (Exception $e) {
            throw new RuntimeException("Module configuration for '$module' does not exist or is not parseable");
        }

        if (empty($config) || empty($config['virtualFields'])) {
            throw new RuntimeException("Virtual fields configuration for module '$module' is empty");
        }

        if (!in_array($field, array_keys($config['virtualFields']))) {
            throw new RuntimeException("Field '$field' is not defined as virtual for module '$module'");
        }

        return true;
    }
}

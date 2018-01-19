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

class RealModuleFieldCheck extends BaseCheck
{
    /**
     * Check if the field is defined in the module migration config
     *
     * If the migration file does not exist or is not parseable,
     * we don't have a way to verify, so we assume the field is
     * real.  Only if the config exists, is parseable, and the
     * given field is not defined in it, we return false.
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

        $moduleFields = [];
        try {
            $mc = new ModuleConfig(ConfigType::MIGRATION(), $module);
            $moduleFields = json_decode(json_encode($mc->parse()), true);
        } catch (Exception $e) {
            return true;
        }

        if (empty($moduleFields)) {
            return true;
        }

        foreach ($moduleFields as $moduleField) {
            if ($field == $moduleField['name']) {
                return true;
            }
        }

        throw new RuntimeException("Field '$field' is not defined in migration config of module '$module'");
    }
}

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

class ValidModuleFieldCheck extends BaseCheck
{
    /**
     * Check if the field is valid in the module
     *
     * Valid fields are either real or virtual.
     *
     * @throws \InvalidArgumentException when data is empty or incorrect
     * @throws \RuntimeException when data does not pass the check
     * @param array $data Data to check
     * @return bool Always true
     */
    public static function isOk(array $data)
    {
        $message = '';

        $real = false;
        try {
            $real = RealModuleFieldCheck::isOk($data);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        if ($real) {
            return true;
        }

        $virtual = false;
        try {
            $virtual = VirtualModuleFieldCheck::isOk($data);
        } catch (Exception $e) {
            if (!$message) {
                $message = $e->getMessage();
            }
        }

        if ($virtual) {
            return true;
        }

        throw new RuntimeException($message);
    }
}

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

abstract class BaseCheck implements CheckInterface
{
    /**
     * Main check functionality
     *
     * @throws \InvalidArgumentException when data is empty or incorrect
     * @throws \RuntimeException when data does not pass the check
     * @param array $data Data to check
     * @return bool Always true
     */
    abstract public static function isOk(array $data);
}

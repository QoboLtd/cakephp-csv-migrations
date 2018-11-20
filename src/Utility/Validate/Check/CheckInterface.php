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
namespace CsvMigrations\Utility\Validate\Check;

interface CheckInterface
{
    /**
     * Execute a check
     *
     * @param string $module Module name
     * @param mixed[] $options Check options
     * @return int Number of encountered errors
     */
    public function run(string $module, array $options = []) : int;

    /**
     * Get errors
     *
     * @return string[] List of errors
     */
    public function getErrors() : array;

    /**
     * Get warnings
     *
     * @return string[] List of warnings
     */
    public function getWarnings() : array;
}

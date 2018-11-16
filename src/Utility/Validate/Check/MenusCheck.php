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

use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class MenusCheck extends AbstractCheck
{
    /**
     * Execute a check
     *
     * @param string $module Module name
     * @param array $options Check options
     * @return int Number of encountered errors
     */
    public function run($module, array $options = []) : int
    {
        $mc = new ModuleConfig(ConfigType::MENUS(), $module, null, ['cacheSkip' => true]);
        $config = [];
        try {
            $mc->parse();
        } catch (InvalidArgumentException $e) {
            // We need errors and warnings irrelevant of the exception
            $this->errors = array_merge($this->errors, $mc->getErrors());
        }
        $this->warnings = array_merge($this->warnings, $mc->getWarnings());

        return count($this->errors);
    }
}

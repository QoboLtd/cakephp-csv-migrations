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

use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use RuntimeException;

class ValidListCheck extends BaseCheck
{
    /**
     * Check if the given list is valid
     *
     * List name can be either plain, like 'genders',
     * or include a module name, like 'Leads.sources'.
     * If module is unknown, null is assumed.  Lists
     * wth no values are considered invalid.
     *
     * @throws \InvalidArgumentException when data is empty or incorrect
     * @throws \RuntimeException when data does not pass the check
     * @param array $data Data to check
     * @return bool Always true
     */
    public static function isOk(array $data)
    {
        if (empty($data['list'])) {
            throw new InvalidArgumentException("'list' parameter is not specified");
        }

        $list = trim($data['list']);

        if (empty($list)) {
            throw new InvalidArgumentException("'list' parameter is empty");
        }

        $module = null;
        if (strpos($list, '.') !== false) {
            list($module, $list) = explode('.', $list, 2);
        }

        $listItems = [];
        try {
            $mc = new ModuleConfig(ConfigType::LISTS(), $module, $list);
            $listItems = $mc->parse()->items;
        } catch (Exception $e) {
            throw new RuntimeException("Could not parse config for list '" . $data['list'] . "'");
        }

        if (empty($listItems)) {
            throw new RuntimeException("List '" . $data['list'] . "' has no items");
        }

        return true;
    }
}

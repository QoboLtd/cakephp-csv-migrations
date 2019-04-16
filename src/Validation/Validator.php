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
namespace CsvMigrations\Validation;

use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * This class can be attached to CakePHP's validator and used for validating
 * model fields.
 */
class Validator
{
    /**
     * Returns true if the passed list item is valid and enabled.
     *
     * @param string $item List item.
     * @param string $moduleName Module name.
     * @param string $listName List name.
     * @return bool|string True if list item is enabled, error string otherwise.
     */
    public function inModuleList(string $item, string $moduleName, string $listName)
    {
        try {
            $items = (new ModuleConfig(ConfigType::LISTS(), $moduleName, $listName))->parseToArray();
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }

        /** @var mixed[]|null $config */
        $inactive = $items['items'][$item]['inactive'] ?? null;
        if ($inactive === null) {
            return (string)__('Invalid list item: `{0}`', $item);
        }

        return !(bool)$inactive;
    }
}

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

use Qobo\Utils\Module\Exception\MissingModuleException;
use Qobo\Utils\Module\ModuleRegistry;

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
     * @return string|true True if list item is enabled, error string otherwise.
     */
    public function inModuleList(string $item, string $moduleName, string $listName)
    {
        try {
            $items = ModuleRegistry::getModule($moduleName)->getList($listName);
        } catch (MissingModuleException $e) {
            return $e->getMessage();
        }

        /** @var mixed[]|null $config */
        $inactive = $items[$item]['inactive'] ?? null;
        if ($inactive === null || $inactive === true) {
            return (string)__d('Qobo/CsvMigrations', 'Invalid list item: `{0}`', $item);
        }

        return true;
    }
}

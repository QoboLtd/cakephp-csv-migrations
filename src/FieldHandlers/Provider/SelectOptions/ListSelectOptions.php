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

namespace CsvMigrations\FieldHandlers\Provider\SelectOptions;

use Cake\Core\App;
use Qobo\Utils\Module\Exception\MissingModuleException;
use Qobo\Utils\Module\ModuleRegistry;

/**
 * ListSelectOptions
 *
 * List select options
 */
class ListSelectOptions extends AbstractSelectOptions
{
    /**
     * Provide select options
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return array
     */
    public function provide($data = null, array $options = []): array
    {
        $spacer = isset($options['spacer']) ? (string)$options['spacer'] : ' - ';
        $flatten = isset($options['flatten']) ? (bool)$options['flatten'] : true;

        list($module, $list) = false !== strpos($data, '.') ?
            explode('.', $data, 2) :
            [$this->config->getTable()->getAlias(), $data ?? ''];

        try {
            $result = ModuleRegistry::getModule($module)->getList($list, $flatten, true);
        } catch (MissingModuleException $e) {
            return [];
        }
        if (empty($result)) {
            return [];
        }

        if (! $flatten) {
            return $result;
        }

        foreach ($result as $key => $value) {
            $result[$key] = str_repeat($spacer, substr_count((string)$key, '.')) . $value['label'];
        }

        return $result;
    }
}

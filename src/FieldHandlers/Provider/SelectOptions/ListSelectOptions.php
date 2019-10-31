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
use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

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
    public function provide($data = null, array $options = []) : array
    {
        $spacer = isset($options['spacer']) ? (string)$options['spacer'] : ' - ';
        $flatten = isset($options['flatten']) ? (bool)$options['flatten'] : true;
        $currentValue = isset($options['value']) ? $options['value'] : false;

        list($module, $list) = false !== strpos($data, '.') ?
            explode('.', $data, 2) :
            [App::shortName(get_class($this->config->getTable()), 'Model/Table', 'Table'), $data];

        $result = [];
        try {
            $config = new ModuleConfig(ConfigType::LISTS(), $module, $list, [
                    'flatten' => $flatten,
                    'filter' => true,
                    'transition' => $currentValue
                ]);
            $config = $config->parse();
            if (! property_exists($config, 'items')) {
                return [];
            }

            $config = json_encode($config->items);
            if (false === $config) {
                return [];
            }

            $result = json_decode($config, true);
        } catch (InvalidArgumentException $e) {
            /* Do nothing.
             *
             * ModuleConfig checks for the file to exist and to be readable and so on, but here we do load lists
             * recursively (for sub-lists, etc), which might result in files not always being there.
             *
             * In this particular case, it's not the end of the world.
             */
            return [];
        }

        if (empty($result)) {
            return [];
        }

        if (! $flatten) {
            return $result;
        }

        foreach ($result as $key => $value) {
            $result[$key] = str_repeat($spacer, substr_count($key, '.')) . $value['label'];
        }

        return $result;
    }
}

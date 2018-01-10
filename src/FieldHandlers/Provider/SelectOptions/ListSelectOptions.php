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

use Cake\Collection\Collection;
use Exception;
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
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $listName = $data;
        $spacer = isset($options['spacer']) ? $options['spacer'] : ' - ';
        $flatten = isset($options['flatten']) ? $options['flatten'] : true;

        $result = $this->getSelectOptions($listName, $spacer, $flatten);

        return $result;
    }

    /**
     * Method that retrieves select input options by list name.
     *
     * @param string $listName list name
     * @param string $spacer The string to use for prefixing the values according to
     * their depth in the tree
     * @param bool $flatten flat list flag
     * @return array list options
     */
    protected function getSelectOptions($listName, $spacer = ' - ', $flatten = true)
    {
        $result = $this->getListFieldOptions($listName);
        $result = $this->filterOptions($result);

        if (!$flatten) {
            return $result;
        }

        // flatten list options
        $collection = new Collection($result);
        $result = $collection->listNested()->printer('name', 'id', $spacer)->toArray();

        return $result;
    }

    /**
     * Method that retrieves list field options.
     *
     * @param  string $listName list name
     * @param  string $prefix   nested option prefix
     * @return array
     */
    protected function getListFieldOptions($listName, $prefix = null)
    {
        $result = [];

        $module = null;
        if (strpos($listName, '.') !== false) {
            list($module, $listName) = explode('.', $listName, 2);
        }
        $listData = [];
        try {
            $mc = new ModuleConfig(ConfigType::LISTS(), $module, $listName);
            $listData = $mc->parse()->items;
            $result = json_decode(json_encode($listData), true);
        } catch (Exception $e) {
            /* Do nothing.
             *
             * ModuleConfig checks for the
             * file to exist and to be readable and so on,
             * but here we do load lists recursively (for
             * sub-lists, etc), which might result in files
             * not always being there.
             *
             * In this particular case, it's not the end of the
             * world.
             */
        }
        $result = $this->prepareListOptions($result);

        return $result;
    }

    /**
     * Method that filters list options, excluding non-active ones
     *
     * @param  array  $options list options
     * @param  int    $index nested list index
     * @param  string $parent parent id
     * @return array
     */
    protected function filterOptions($options, $index = -1, $parent = null)
    {
        $result = [];
        foreach ($options as $k => $v) {
            if ($v['inactive']) {
                continue;
            }
            $index++;
            $result[$index] = ['id' => $k, 'parent_id' => $parent, 'name' => $v['label']];
            /*
            iterate over children options
             */
            if (isset($v['children'])) {
                $result[$index]['children'] = $this->filterOptions($v['children'], $index, $k);
            }
        }

        return $result;
    }

    /**
     * Method that restructures list options csv data for better handling.
     *
     * @param  array  $data     csv data
     * @param  string $prefix   nested option prefix
     * @return array
     */
    protected function prepareListOptions($data, $prefix = null)
    {
        $result = [];

        if ($prefix) {
            $prefix .= '.';
        }

        foreach ($data as $item) {
            $fixedItem = $item;
            $fixedItem['inactive'] = (bool)$item['inactive'];
            if (!empty($item['children'])) {
                $fixedItem['children'] = $this->prepareListOptions($item['children'], $prefix . $item['value']);
            }
            $result[$prefix . $item['value']] = $fixedItem;
        }

        return $result;
    }
}

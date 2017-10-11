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
namespace CsvMigrations\FieldHandlers;

use Cake\Collection\Collection;
use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\BaseListFieldHandler;
use Exception;
use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * BaseCsvListFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to list field handlers, which rely on CSV
 * files and use dot notation to store nested values.
 */
abstract class BaseCsvListFieldHandler extends BaseListFieldHandler
{
    /**
     * Renderer to use
     */
    const RENDERER = 'list';

    /**
     * Render field value
     *
     * This method prepares the output of the value for the given
     * field.  The result can be controlled via the variety of
     * options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field value
     */
    public function renderValue($data, array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $result = $this->_getFieldValueFromData($data);

        if (empty($result)) {
            return $result;
        }

        if (!empty($options['listItems'])) {
            return parent::renderValue($result, $options);
        }

        if (empty($options['fieldDefinitions'])) {
            throw new InvalidArgumentException("No listItems or fieldDefinitions options provided");
        }

        $options['listItems'] = $this->_getSelectOptions($options['fieldDefinitions']->getLimit());

        return parent::renderValue($result, $options);
    }

    /**
     * Get options for field search
     *
     * This method prepares an array of search options, which includes
     * label, form input, supported search operators, etc.  The result
     * can be controlled with a variety of options.
     *
     * @param  array  $options Field options
     * @return array           Array of field input HTML, pre and post CSS, JS, etc
     */
    public function getSearchOptions(array $options = [])
    {
        // Fix options as early as possible
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $result = parent::getSearchOptions($options);
        if (empty($result[$this->field]['input'])) {
            return $result;
        }

        $selectOptions = ['' => static::EMPTY_OPTION_LABEL];
        $selectOptions += $this->_getSelectOptions($options['fieldDefinitions']->getLimit());

        $content = $this->cakeView->Form->select('{{name}}', $selectOptions, [
            'class' => 'form-control',
            'label' => false
        ]);

        $result[$this->field]['input'] = [
            'content' => $content
        ];

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
    protected function _getSelectOptions($listName, $spacer = ' - ', $flatten = true)
    {
        $result = $this->__getListFieldOptions($listName);
        $result = $this->__filterOptions($result);

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
    private function __getListFieldOptions($listName, $prefix = null)
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
        $result = $this->__prepareListOptions($result);

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
    private function __filterOptions($options, $index = -1, $parent = null)
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
                $result[$index]['children'] = $this->__filterOptions($v['children'], $index, $k);
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
    private function __prepareListOptions($data, $prefix = null)
    {
        $result = [];

        if ($prefix) {
            $prefix .= '.';
        }

        foreach ($data as $item) {
            $fixedItem = $item;
            $fixedItem['inactive'] = (bool)$item['inactive'];
            if (!empty($item['children'])) {
                $fixedItem['children'] = $this->__prepareListOptions($item['children'], $prefix . $item['value']);
            }
            $result[$prefix . $item['value']] = $fixedItem;
        }

        return $result;
    }
}

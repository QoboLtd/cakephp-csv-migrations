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
namespace CsvMigrations\Utility;

use Cake\Core\App;
use Cake\ORM\Table;
use CsvMigrations\FieldHandlers\CsvField;
use Exception;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class Field
{
    /**
     * Get Table's lookup fields.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return array
     */
    public static function getLookup(Table $table)
    {
        $moduleName = App::shortName(get_class($table), 'Model/Table', 'Table');

        $config = new ModuleConfig(ConfigType::MODULE(), $moduleName);
        $parsed = $config->parse();

        return $parsed->table->lookup_fields ?: [];
    }

    /**
     * Get Table's csv fields.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return array
     */
    public static function getCsv(Table $table)
    {
        $moduleName = App::shortName(get_class($table), 'Model/Table', 'Table');

        $config = new ModuleConfig(ConfigType::MIGRATION(), $moduleName);
        $parsed = json_decode(json_encode($config->parse()), true);

        if (empty($parsed)) {
            return [];
        }

        $result = [];
        foreach ($parsed as $field => $params) {
            $result[$field] = new CsvField($params);
        }

        return $result;
    }

    /**
     * Get View's csv fields.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $action Controller action
     * @param bool $includeModel Include model flag
     * @param bool $panels Arrange panels flag
     * @return array
     */
    public static function getCsvView(Table $table, $action, $includeModel = false, $panels = false)
    {
        $tableName = App::shortName(get_class($table), 'Model/Table', 'Table');

        $config = new ModuleConfig(ConfigType::VIEW(), $tableName, $action);
        $result = $config->parse()->items;

        if ((bool)$panels) {
            $result = static::arrangePanels($result);
        }

        if ((bool)$includeModel) {
            $result = static::setFieldPluginAndModel($tableName, $result);
        }

        return $result;
    }

    /**
     * Get list's options.
     *
     * @param string $listName List name
     * @param bool $flat Fatten list flag
     * @param string $prefix Option value prefix
     * @return array
     */
    public static function getList($listName, $flat = false, $prefix = '')
    {
        $moduleName = null;
        if (false !== strpos($listName, '.')) {
            list($moduleName, $listName) = explode('.', $listName, 2);
        }

        $config = new ModuleConfig(ConfigType::LISTS(), $moduleName, $listName);
        try {
            $items = $config->parse()->items;
        } catch (Exception $e) {
            return [];
        }

        if (empty($items)) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            $key = $prefix . $item->value;

            $result[$key] = [
                'label' => $item->label,
                'inactive' => (bool)$item->inactive
            ];
            // recursive call to fetch children
            $children = static::getList($listName . DS . $item->value, $flat, $key . '.');
            if (empty($children)) {
                continue;
            }

            $result[$key]['children'] = $children;
        }

        if ($flat) {
            $result = static::_flattenList($result);
        }

        return $result;
    }

    /**
     * Flatten list options.
     *
     * @param array $options List options
     * @return array
     */
    protected static function _flattenList(array $options)
    {
        $result = [];
        foreach ($options as $field => $params) {
            if (empty($params['children'])) {
                $result[$field] = $params;
                continue;
            }

            $children = $params['children'];
            unset($params['children']);
            $result[$field] = $params;
            foreach ($children as $childField => $childParams) {
                $result[$childField] = $childParams;
            }
        }

        return $result;
    }

    /**
     * Method that arranges csv fields into panels.
     *
     * @param array $fields csv fields
     * @return array
     */
    protected static function arrangePanels(array $fields)
    {
        $result = [];

        foreach ($fields as $row) {
            $panelName = array_shift($row);
            $result[$panelName][] = $row;
        }

        return $result;
    }

    /**
     * Add plugin and model name for each of the csv fields.
     *
     * @param string $tableName Table name
     * @param array $fields View csv fields
     * @return array
     */
    protected static function setFieldPluginAndModel($tableName, array $fields)
    {
        list($plugin, $model) = pluginSplit($tableName);

        $callback = function (&$value, $key) use ($plugin, $model, &$callback) {
            $value = ['plugin' => $plugin, 'model' => $model, 'name' => $value];

            return $value;
        };

        array_walk_recursive($fields, $callback);

        return $fields;
    }
}

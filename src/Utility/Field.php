<?php
namespace CsvMigrations\Utility;

use Cake\Core\App;
use Cake\ORM\Table;
use CsvMigrations\FieldHandlers\CsvField;
use Exception;
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

        $config = new ModuleConfig(ModuleConfig::CONFIG_TYPE_MODULE, $moduleName);
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

        $config = new ModuleConfig(ModuleConfig::CONFIG_TYPE_MIGRATION, $moduleName);
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

        $config = new ModuleConfig(ModuleConfig::CONFIG_TYPE_LIST, $moduleName, $listName);
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
}

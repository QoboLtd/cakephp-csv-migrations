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
use Cake\Datasource\RepositoryInterface;
use CsvMigrations\FieldHandlers\CsvField;
use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class Field
{
    /**
     * Get Table's lookup fields.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return string[]
     */
    public static function getLookup(RepositoryInterface $table): array
    {
        $moduleName = App::shortName(get_class($table), 'Model/Table', 'Table');

        $config = new ModuleConfig(ConfigType::MODULE(), $moduleName);
        $parsed = $config->parseToArray();

        if (! array_key_exists('table', $parsed)) {
            return [];
        }

        if (! array_key_exists('lookup_fields', $parsed['table'])) {
            return [];
        }

        return $parsed['table']['lookup_fields'];
    }

    /**
     * Get Table's csv fields.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return mixed[]
     */
    public static function getCsv(RepositoryInterface $table): array
    {
        $moduleName = App::shortName(get_class($table), 'Model/Table', 'Table');

        $config = new ModuleConfig(ConfigType::MIGRATION(), $moduleName);
        $parsed = $config->parseToArray();
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
     * CSV field instance getter.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param string $field Field name
     * @return \CsvMigrations\FieldHandlers\CsvField|null
     */
    public static function getCsvField(RepositoryInterface $table, string $field): ?CsvField
    {
        if ('' === $field) {
            return null;
        }

        $moduleName = App::shortName(get_class($table), 'Model/Table', 'Table');

        $config = new ModuleConfig(ConfigType::MIGRATION(), $moduleName);
        $parsed = $config->parseToArray();
        if (!isset($parsed[$field])) {
            return null;
        }

        return new CsvField($parsed);
    }

    /**
     * Module virtual fields getter.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @return string[]
     */
    public static function getVirtual(RepositoryInterface $table): array
    {
        $moduleName = App::shortName(get_class($table), 'Model/Table', 'Table');
        $config = (new ModuleConfig(ConfigType::MODULE(), $moduleName))->parseToArray();

        return isset($config['virtualFields']) ? (array)$config['virtualFields'] : [];
    }

    /**
     * Get View's csv fields.
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param string $action Controller action
     * @param bool $includeModel Include model flag
     * @param bool $panels Arrange panels flag
     * @return mixed[]
     */
    public static function getCsvView(RepositoryInterface $table, string $action, bool $includeModel = false, bool $panels = false): array
    {
        $tableName = App::shortName(get_class($table), 'Model/Table', 'Table');

        $config = (new ModuleConfig(ConfigType::VIEW(), $tableName, $action))->parseToArray();

        if (! isset($config['items'])) {
            return [];
        }

        $result = $config['items'];

        if ($panels) {
            $result = static::arrangePanels($result);
        }

        if ($includeModel) {
            $result = static::setFieldPluginAndModel($tableName, $result);
        }

        return $result;
    }

    /**
     * Get list's options.
     *
     * @param string $listName List name
     * @param bool $flat Fatten list flag
     * @return mixed[]
     */
    public static function getList(string $listName, bool $flat = false): array
    {
        $moduleName = '';
        if (false !== strpos($listName, '.')) {
            list($moduleName, $listName) = explode('.', $listName, 2);
        }

        $config = new ModuleConfig(ConfigType::LISTS(), $moduleName, $listName, ['flatten' => $flat]);
        try {
            $config = $config->parseToArray();
            $items = isset($config['items']) ? $config['items'] : [];
        } catch (InvalidArgumentException $e) {
            return [];
        }

        return $items;
    }

    /**
     * Method that arranges csv fields into panels.
     *
     * @param mixed[] $fields csv fields
     * @return mixed[]
     */
    protected static function arrangePanels(array $fields): array
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
     * @param string[] $fields View csv fields
     * @return mixed[]
     */
    protected static function setFieldPluginAndModel(string $tableName, array $fields): array
    {
        list($plugin, $model) = pluginSplit($tableName);

        $callback = function (&$value, $key) use ($plugin, $model) {
            $value = ['plugin' => $plugin, 'model' => $model, 'name' => $value];

            return $value;
        };

        array_walk_recursive($fields, $callback);

        return $fields;
    }
}

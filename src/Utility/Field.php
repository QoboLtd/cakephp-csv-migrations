<?php
namespace CsvMigrations\Utility;

use Cake\Core\App;
use Cake\ORM\Table;
use CsvMigrations\FieldHandlers\CsvField;
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
}

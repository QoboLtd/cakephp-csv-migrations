<?php
namespace CsvMigrations\Utility;

use Cake\Core\App;
use Cake\ORM\Table;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class Field
{
    /**
     * Get Table's lookup fields.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return array
     */
    public static function getLookupFields(Table $table)
    {
        $moduleName = App::shortName(get_class($table), 'Model/Table', 'Table');

        $config = new ModuleConfig(ModuleConfig::CONFIG_TYPE_MODULE, $moduleName);
        $parsed = $config->parse();

        return $parsed->table->lookup_fields ?: [];
    }
}

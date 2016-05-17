<?php
namespace CsvMigrations;

use Cake\Utility\Inflector;

class CsvMigrationsUtils
{
    /**
     * Method that generates association naming based on passed parameters.
     *
     * @param  string $module     module name
     * @param  string $foreignKey foreign key name
     * @return string
     */
    static public function createAssociationName($module, $foreignKey = '')
    {
        list($plugin, $model) = pluginSplit($module);
        if ('' !== $foreignKey) {
            $foreignKey = Inflector::camelize($foreignKey);
        }
        return $foreignKey . $plugin . $model;
    }
}
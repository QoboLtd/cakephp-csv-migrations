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
    public static function createAssociationName($module, $foreignKey = '')
    {
        list($plugin, $model) = pluginSplit($module);
        if ('' !== $foreignKey) {
            $foreignKey = Inflector::camelize($foreignKey);
        }
        $pos = strpos($plugin, '/');
        if ($pos) {
            $plugin = substr($plugin, $pos + 1);
        }

        return $foreignKey . $plugin . $model;
    }
}

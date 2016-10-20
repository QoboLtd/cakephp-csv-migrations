<?php
namespace CsvMigrations\PathFinder;

use Cake\Core\Configure;

/**
 * ConfigPathFinder Class
 *
 * This path finder is here to assist with finding
 * the paths to the module configuration files.  If
 * no $path is specified, then the path to the
 * default configuration file (config.ini) is
 * returned.
 *
 * @author Leonid Mamchenkov <l.mamchenkov@qobo.biz>
 */
class ConfigPathFinder extends BasePathFinder
{
    protected $pathConfigKey = 'CsvMigrations.migrations.path';
    protected $fileName = 'config.ini';
}

<?php
namespace CsvMigrations\PathFinder;

use Cake\Core\Configure;

class ConfigPathFinder extends BasePathFinder
{
    protected $pathConfigKey = 'CsvMigrations.migrations.path';
    protected $fileName = 'config.ini';
}

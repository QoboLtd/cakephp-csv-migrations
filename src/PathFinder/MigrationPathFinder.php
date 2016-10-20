<?php
namespace CsvMigrations\PathFinder;

use Cake\Core\Configure;

class MigrationPathFinder extends BasePathFinder
{
    protected $pathConfigKey = 'CsvMigrations.migrations.path';
    protected $fileName = 'migration.csv';
}

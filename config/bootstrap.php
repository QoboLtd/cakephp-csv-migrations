<?php
use Burzum\FileStorage\Storage\Listener\BaseListener;
use Burzum\FileStorage\Storage\StorageManager;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use CsvMigrations\Events\ViewMenuListener;

Configure::write('CsvMigrations.migrations.path', CONFIG . 'CsvMigrations' . DS . 'migrations' . DS);
Configure::write('CsvMigrations.views.path', CONFIG . 'CsvMigrations' . DS . 'views' . DS);
Configure::write('CsvMigrations.lists.path', CONFIG . 'CsvMigrations' . DS . 'lists' . DS);
Configure::write('CsvMigrations.migrations.filename', 'migration');
Configure::write('CsvMigrations.typeahead.min_length', 1);
Configure::write('CsvMigrations.typeahead.timeout', 300);
Configure::write('CsvMigrations.acl', [
    'class' => null, // currently only accepts Table class with prefixed plugin name. Example: 'MyPlugin.TableName'
    'method' => null
]);
Configure::write('CsvMigrations.api', [
    'auth' => true,
    'token' => null,
    'menus_property' => '_Menus',
    'excluded_menus' => [
        'index' => ['top']
    ]
]);

EventManager::instance()->on(new ViewMenuListener());

//Load upload plugin configuration
include 'file_storage.php';

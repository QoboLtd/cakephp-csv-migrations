<?php
use Burzum\FileStorage\Storage\Listener\BaseListener;
use Burzum\FileStorage\Storage\StorageManager;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use CsvMigrations\Events\AddViewListener;
use CsvMigrations\Events\EditViewListener;
use CsvMigrations\Events\IndexViewListener;
use CsvMigrations\Events\ViewMenuListener;
use CsvMigrations\Events\ViewViewListener;

Configure::write('CsvMigrations.migrations.path', CONFIG . 'CsvMigrations' . DS . 'migrations' . DS);
Configure::write('CsvMigrations.views.path', CONFIG . 'CsvMigrations' . DS . 'views' . DS);
Configure::write('CsvMigrations.lists.path', CONFIG . 'CsvMigrations' . DS . 'lists' . DS);
Configure::write('CsvMigrations.migrations.filename', 'migration');
Configure::write('CsvMigrations.typeahead.min_length', 1);
Configure::write('CsvMigrations.typeahead.timeout', 300);
Configure::write('CsvMigrations.acl', [
    'class' => null, // currently only accepts Table class with prefixed plugin name. Example: 'MyPlugin.TableName'
    'method' => null,
    'component' => null
]);
Configure::write('CsvMigrations.api', [
    'auth' => true,
    'token' => null
]);

EventManager::instance()->on(new AddViewListener());
EventManager::instance()->on(new EditViewListener());
EventManager::instance()->on(new IndexViewListener());
EventManager::instance()->on(new ViewMenuListener());
EventManager::instance()->on(new ViewViewListener());

//Load upload plugin configuration
include 'file_storage.php';

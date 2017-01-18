<?php
use Burzum\FileStorage\Storage\Listener\BaseListener;
use Burzum\FileStorage\Storage\StorageManager;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use CsvMigrations\Events\AddViewListener;
use CsvMigrations\Events\EditViewListener;
use CsvMigrations\Events\IndexViewListener;
use CsvMigrations\Events\LayoutListener;
use CsvMigrations\Events\LookupListener;
use CsvMigrations\Events\ModelAfterSaveListener;
use CsvMigrations\Events\ReportListener;
use CsvMigrations\Events\ViewViewListener;
use CsvMigrations\Events\ViewViewTabsListener;

Configure::write('CsvMigrations.actions', ['index', 'view', 'add', 'edit']);
Configure::write('CsvMigrations.migrations.path', CONFIG . 'CsvMigrations' . DS . 'migrations' . DS);
Configure::write('CsvMigrations.views.path', CONFIG . 'CsvMigrations' . DS . 'views' . DS);
Configure::write('CsvMigrations.lists.path', CONFIG . 'CsvMigrations' . DS . 'lists' . DS);
Configure::write('CsvMigrations.migrations.filename', 'migration');
Configure::write('CsvMigrations.reports.filename', 'reports');
Configure::write('CsvMigrations.default_icon', 'cube');
Configure::write('CsvMigrations.select2', [
    'min_length' => 0,
    'timeout' => 300,
    'id' => '[data-type="select2"]',
    'limit' => 10
]);
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
EventManager::instance()->on(new LayoutListener());
EventManager::instance()->on(new LookupListener());
EventManager::instance()->on(new ViewViewListener());
EventManager::instance()->on(new ReportListener());
EventManager::instance()->on(new ViewViewTabsListener());
EventManager::instance()->on(new ModelAfterSaveListener());

//Load upload plugin configuration
include 'file_storage.php';
// Load bootstrap-fileinput configuration
include 'file_upload.php';

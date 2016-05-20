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

EventManager::instance()->on(new ViewMenuListener());

StorageManager::config(
    'Local',
    [
        'adapterOptions' => [WWW_ROOT, true],
        'adapterClass' => '\Gaufrette\Adapter\Local',
        'class' => '\Gaufrette\Filesystem'
    ]
);
$listener = new BaseListener([
    'pathBuilderOptions' => [
        'pathPrefix' => '/uploads'
    ]
]);

EventManager::instance()->on($listener);
<?php
use Burzum\FileStorage\Storage\Listener\BaseListener;
use Burzum\FileStorage\Storage\StorageManager;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use CsvMigrations\Event\AddViewListener;
use CsvMigrations\Event\EditViewListener;
use CsvMigrations\Event\IndexViewListener;
use CsvMigrations\Event\LayoutListener;
use CsvMigrations\Event\LookupListener;
use CsvMigrations\Event\ModelAfterSaveListener;
use CsvMigrations\Event\Model\AutoIncrementEventListener;
use CsvMigrations\Event\ReportListener;
use CsvMigrations\Event\ViewViewListener;
use CsvMigrations\Event\ViewViewTabsListener;

/**
 * Plugin configuration
 */
// get app level config
$config = Configure::read('CsvMigrations');
$config = $config ? $config : [];

// load default plugin config
Configure::load('CsvMigrations.csv_migrations');

// overwrite default plugin config by app level config
Configure::write('CsvMigrations', array_replace_recursive(
    Configure::read('CsvMigrations'),
    $config
));

EventManager::instance()->on(new AutoIncrementEventListener());
EventManager::instance()->on(new AddViewListener());
EventManager::instance()->on(new EditViewListener());
EventManager::instance()->on(new IndexViewListener());
EventManager::instance()->on(new LayoutListener());
EventManager::instance()->on(new LookupListener());
EventManager::instance()->on(new ViewViewListener());
EventManager::instance()->on(new ReportListener());
EventManager::instance()->on(new ViewViewTabsListener());
EventManager::instance()->on(new ModelAfterSaveListener());

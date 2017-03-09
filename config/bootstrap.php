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
use CsvMigrations\Events\Model\AutoIncrementEventListener;
use CsvMigrations\Events\ReportListener;
use CsvMigrations\Events\ViewViewListener;
use CsvMigrations\Events\ViewViewTabsListener;

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

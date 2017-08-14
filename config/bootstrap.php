<?php
use Burzum\FileStorage\Storage\Listener\BaseListener;
use Burzum\FileStorage\Storage\StorageManager;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use CsvMigrations\Event\Controller\Api\AddActionListener;
use CsvMigrations\Event\Controller\Api\EditActionListener;
use CsvMigrations\Event\Controller\Api\IndexActionListener;
use CsvMigrations\Event\Controller\Api\LookupActionListener;
use CsvMigrations\Event\Controller\Api\ViewActionListener;
use CsvMigrations\Event\Model\AutoIncrementEventListener;
use CsvMigrations\Event\Model\ModelAfterSaveListener;
use CsvMigrations\Event\Plugin\Search\View\ReportListener;
use CsvMigrations\Event\View\ViewViewTabsListener;

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

// get app level config
$config = Configure::read('Importer');
$config = $config ? $config : [];

// load default plugin config
Configure::load('CsvMigrations.importer');

// overwrite default plugin config by app level config
Configure::write('Importer', array_replace_recursive(
    Configure::read('Importer'),
    $config
));

EventManager::instance()->on(new AddActionListener());
EventManager::instance()->on(new AutoIncrementEventListener());
EventManager::instance()->on(new EditActionListener());
EventManager::instance()->on(new IndexActionListener());
EventManager::instance()->on(new LookupActionListener());
EventManager::instance()->on(new ModelAfterSaveListener());
EventManager::instance()->on(new ReportListener());
EventManager::instance()->on(new ViewActionListener());
EventManager::instance()->on(new ViewViewTabsListener());

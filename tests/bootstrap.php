<?php

use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Mailer\Email;
use Cake\TestSuite\ConsoleIntegrationTestCase;
use Cake\Utility\Security;

$pluginName = 'CsvMigrations';
if (empty($pluginName)) {
    throw new \RuntimeException("Plugin name is not configured");
}

/*
 * Test suite bootstrap
 *
 * This function is used to find the location of CakePHP whether CakePHP
 * has been installed as a dependency of the plugin, or the plugin is itself
 * installed as a dependency of an application.
 */
$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);
    throw new \RuntimeException("Failed to find CakePHP");
};

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
define('ROOT', $findRoot(__FILE__));
define('APP_DIR', 'App');
define('WEBROOT_DIR', 'webroot');
define('APP', ROOT . '/tests/App/');
define('CONFIG', ROOT . '/tests/config/');
define('WWW_ROOT', ROOT . DS . WEBROOT_DIR . DS);
define('TESTS', ROOT . DS . 'tests' . DS);
define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);

require ROOT . '/vendor/autoload.php';
require CORE_PATH . 'config/bootstrap.php';

mb_internal_encoding('UTF-8');

Configure::write('App', [
    'namespace' => $pluginName . '\Test\App',
    'encoding' => 'UTF-8',
    'paths' => [
        'templates' => [
            APP . 'Template' . DS,
        ],
    ],
]);
Configure::write('debug', true);
Security::setSalt('C14#E9YY0t*QZ2M9Ia4D6swLJ8507Kai47C14#E9YY0t*QZ2M9Ia4D6swLJ8507Kai47');

$TMP = new Folder(TMP);
$TMP->create(TMP . 'cache/models', 0777);
$TMP->create(TMP . 'cache/persistent', 0777);
$TMP->create(TMP . 'cache/views', 0777);

$cache = [
    'default' => [
        'engine' => 'File',
    ],
    '_cake_core_' => [
        'className' => 'File',
        'prefix' => strtolower($pluginName) . '_myapp_cake_core_',
        'path' => CACHE . 'persistent/',
        'serialize' => true,
        'duration' => '+10 seconds',
    ],
    '_cake_model_' => [
        'className' => 'File',
        'prefix' => strtolower($pluginName) . '_my_app_cake_model_',
        'path' => CACHE . 'models/',
        'serialize' => 'File',
        'duration' => '+10 seconds',
    ],
];

Cake\Cache\Cache::setConfig($cache);
Cake\Core\Configure::write('Session', [
    'defaults' => 'php',
]);

// Ensure default test connection is defined
if (!getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}

Cake\Datasource\ConnectionManager::setConfig('default', [
    'url' => getenv('db_dsn'),
    'quoteIdentifiers' => true,
    'timezone' => 'UTC',
]);

Cake\Datasource\ConnectionManager::setConfig('test', [
    'url' => getenv('db_dsn'),
    'quoteIdentifiers' => true,
    'timezone' => 'UTC',
]);

// Alias AppController to the test App
class_alias($pluginName . '\Test\App\Controller\AppController', 'App\Controller\AppController');
// If plugin has routes.php/bootstrap.php then load them, otherwise don't.
$loadPluginRoutes = file_exists(ROOT . DS . 'config' . DS . 'routes.php');
$loadPluginBootstrap = file_exists(ROOT . DS . 'config' . DS . 'bootstrap.php');
Cake\Core\Plugin::load('Qobo/Utils', [ 'bootstrap' => true ]);
Cake\Core\Plugin::load('CakeDC/Users', ['routes' => true, 'bootstrap' => true ]);
Cake\Core\Plugin::load($pluginName, ['path' => ROOT . DS, 'autoload' => true, 'routes' => $loadPluginRoutes, 'bootstrap' => $loadPluginBootstrap]);

// Load configuration file(s)
Configure::load('CsvMigrations.csv_migrations');
Configure::load('CsvMigrations.importer');

/**
 * Borrowed from CakePHP (3.6.13)
 *
 * @link https://github.com/cakephp/cakephp/blob/3.6.13/tests/TestCase/Mailer/EmailTest.php#L131-L138
 */
Email::setConfigTransport([
    'debug' => [
        'className' => 'Debug',
    ],
]);

/**
 * Borrowed from CakePHP (3.6.13)
 *
 * @link https://github.com/cakephp/cakephp/blob/3.6.13/tests/TestCase/Mailer/EmailTest.php#L2245-L2248
 */
Email::setConfig([
    'default' => [
        'from' => ['some@example.com' => 'My website'],
        'to' => 'test@example.com',
        'subject' => 'Test mail subject',
        'transport' => 'debug',
    ],
]);

// Generate module files for tests
$runner = new class extends ConsoleIntegrationTestCase {
};
$modulesPath = TESTS . 'config' . DS . 'Modules' . DS;
$modulesFolder = new Folder($modulesPath);
foreach ($modulesFolder->read()[0] as $dir) {
    $runner->exec('generate_modules module ' . $dir . ' -f --module-path=' . $modulesPath);
    $runner->assertOutputContains('<success>', sprintf('Failed to generate module: %s', $dir));
}

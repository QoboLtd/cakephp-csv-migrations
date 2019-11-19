<?php

namespace CsvMigrations\Test\App\Config;

use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::defaultRouteClass(DashedRoute::class);

Router::connect('/:controller/:action/*');
Router::plugin(
    'CsvMigrations',
    ['path' => '/csv-migrations'],
    function ($routes) {
        $routes->fallbacks('DashedRoute');
    }
);
// Add api route to handle our REST API functionality
Router::prefix('api', function ($routes) {
    // handle json file extension on API calls
    $routes->setExtensions(['json']);

    $routes->resources('Articles');
    $routes->resources('Leads');

    $routes->fallbacks('DashedRoute');
});

Router::scope('/', function ($routes) {
    $routes->setExtensions(['json']);
    $routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);
    $routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);
});

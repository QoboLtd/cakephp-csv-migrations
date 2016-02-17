<?php
use Cake\Routing\Router;

Router::plugin(
    'CsvMigrations',
    ['path' => '/csv-migrations'],
    function ($routes) {
        $routes->fallbacks('DashedRoute');
    }
);

<?php
use Cake\Routing\Router;

Router::plugin(
    'CsvAssociations',
    ['path' => '/csv-associations'],
    function ($routes) {
        $routes->fallbacks('DashedRoute');
    }
);

<?php
use Cake\Core\Configure;

// Importer plugin configuration
Configure::write('Importer', [
    'path' => TESTS . 'uploads' . DS . 'imports' . DS,
    'max_attempts' => 3
]);

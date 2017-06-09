<?php
use Cake\Core\Configure;

Configure::write('CsvMigrations.actions', ['index', 'view', 'add', 'edit']);
Configure::write('CsvMigrations.modules.path', CONFIG . 'Modules' . DS);
Configure::write('CsvMigrations.api', [
    'auth' => true,
    'token' => null
]);
Configure::write('CsvMigrations.select2', [
    'min_length' => 0,
    'timeout' => 300,
    'id' => '[data-type="select2"]',
    'limit' => 10
]);

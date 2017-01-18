<?php
use Cake\Core\Configure;

Configure::write('CsvMigrations.actions', ['index', 'view', 'add', 'edit']);
Configure::write('CsvMigrations.modules.path', CONFIG . 'Modules' . DS);
Configure::write('CsvMigrations.api', [
    'auth' => true,
    'token' => null
]);

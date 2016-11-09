<?php
use Cake\Core\Configure;

Configure::write('CsvMigrations.actions', ['index', 'view', 'add', 'edit']);
Configure::write('CsvMigrations.migrations.path', CONFIG . 'CsvMigrations' . DS . 'migrations' . DS);
Configure::write('CsvMigrations.views.path', CONFIG . 'CsvMigrations' . DS . 'views' . DS);
Configure::write('CsvMigrations.lists.path', CONFIG . 'CsvMigrations' . DS . 'lists' . DS);
Configure::write('CsvMigrations.migrations.filename', 'migration');
Configure::write('CsvMigrations.typeahead.min_length', 1);
Configure::write('CsvMigrations.typeahead.timeout', 300);
Configure::write('CsvMigrations.api', [
    'auth' => true,
    'token' => null
]);
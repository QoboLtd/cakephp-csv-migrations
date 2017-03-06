<?php
use Cake\Core\Configure;

//@TODO: merge the stuff in one scriptBlock, not dozens;
echo $this->Html->css(
    [
        'AdminLTE./plugins/iCheck/all',
        'AdminLTE./plugins/daterangepicker/daterangepicker-bs3',
        'AdminLTE./plugins/datepicker/datepicker3',
        'AdminLTE./plugins/timepicker/bootstrap-timepicker.min',
        'AdminLTE./plugins/select2/select2.min',
        'CsvMigrations.fileinput.min',
        'CsvMigrations.select2-bootstrap.min',
        'CsvMigrations.style'
    ],
    [
        'block' => 'css'
    ]
);

echo $this->Html->scriptBlock(
    'api_options = ' . json_encode(Configure::read('CsvMigrations.api')) . ';',
    ['block' => 'scriptBotton']
);

$fileInputOptions = Configure::read('CsvMigrations.BootstrapFileInput');
echo $this->Html->scriptBlock(
    'fileInputOptions = ' . json_encode($fileInputOptions) . ';',
    ['block' => 'scriptBotton']
);

echo $this->Html->script(
    [
        'CsvMigrations.dom-observer',
        'CsvMigrations.embedded',
        'CsvMigrations.panels',
        'CsvMigrations.canvas-to-blob.min',
        'CsvMigrations.fileinput.min',
        'CsvMigrations.fileinput-load',
        'CsvMigrations.jquery.dynamicSelect',
        'CsvMigrations.jquery.dynamicSelectInit',
        'AdminLTE./plugins/iCheck/icheck.min',
        'CsvMigrations.icheck.init',
        'AdminLTE./plugins/daterangepicker/moment.min',
        'AdminLTE./plugins/daterangepicker/daterangepicker',
        'CsvMigrations.datetimepicker.init',
        'AdminLTE./plugins/datepicker/bootstrap-datepicker',
        'AdminLTE./plugins/timepicker/bootstrap-timepicker.min',
        'CsvMigrations.timepicker.init',
        'AdminLTE./plugins/select2/select2.full.min',
        'CsvMigrations.select2.init',
        'CsvMigrations.plugin',
    ],
    [
        'block' => 'scriptBotton'
    ]
);

echo $this->Html->scriptBlock(
    'csv_migrations_select2.setup(' . json_encode(
        array_merge(
            Configure::read('CsvMigrations.select2'),
            Configure::read('CsvMigrations.api')
        )
    ) . ');',
    ['block' => 'scriptBotton']
);

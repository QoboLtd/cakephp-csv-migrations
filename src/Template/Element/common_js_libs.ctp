<?php
use Cake\Core\Configure;

echo $this->Html->css(
    [
        'AdminLTE./plugins/iCheck/all',
        'AdminLTE./plugins/daterangepicker/daterangepicker',
        'AdminLTE./plugins/datepicker/datepicker3',
        'AdminLTE./plugins/timepicker/bootstrap-timepicker.min',
        'AdminLTE./plugins/datatables/dataTables.bootstrap',
        'AdminLTE./plugins/select2/select2.min',
        'CsvMigrations.fileinput.min',
        'CsvMigrations.style',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style'
    ],
    [
        'block' => 'css'
    ]
);

echo $this->Html->scriptBlock(
    'api_options = ' . json_encode(Configure::read('CsvMigrations.api')) . ';',
    ['block' => 'scriptBottom']
);

$fileInputOptions = Configure::read('CsvMigrations.BootstrapFileInput');
echo $this->Html->scriptBlock(
    'fileInputOptions = ' . json_encode($fileInputOptions) . ';',
    ['block' => 'scriptBottom']
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
        'AdminLTE./plugins/datatables/jquery.dataTables.min',
        'AdminLTE./plugins/datatables/dataTables.bootstrap.min',
        'CsvMigrations.timepicker.init',
        'AdminLTE./plugins/select2/select2.full.min',
        'CsvMigrations.select2.init',
        'CsvMigrations.plugin',
    ],
    [
        'block' => 'scriptBottom'
    ]
);

echo $this->Html->scriptBlock(
    'csv_migrations_select2.setup(' . json_encode(
        array_merge(
            Configure::read('CsvMigrations.select2'),
            Configure::read('CsvMigrations.api')
        )
    ) . ');',
    ['block' => 'scriptBottom']
);

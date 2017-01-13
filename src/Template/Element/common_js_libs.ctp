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
        'CsvMigrations.embedded',
        'CsvMigrations.panels',
        'CsvMigrations.canvas-to-blob.min',
        'CsvMigrations.fileinput.min',
        'CsvMigrations.fileinput-load',
        'CsvMigrations.jquery.dynamicSelect',
        'CsvMigrations.jquery.dynamicSelectInit',
        'CsvMigrations.jquery.dynamicSelectInit',
        'AdminLTE./plugins/iCheck/icheck.min',
        'AdminLTE./plugins/daterangepicker/moment.min',
        'AdminLTE./plugins/daterangepicker/daterangepicker',
        'AdminLTE./plugins/datepicker/bootstrap-datepicker',
        'AdminLTE./plugins/timepicker/bootstrap-timepicker.min',
        'AdminLTE./plugins/select2/select2.full.min',
        'CsvMigrations.select2'
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

// iCheck for checkbox and radio inputs
echo $this->Html->scriptBlock(
    '$(\'input[type="checkbox"].flat, input[type="radio"].flat\').iCheck({
        checkboxClass: \'icheckbox_flat\',
        radioClass: \'iradio_flat\'
    });
    $(\'input[type="checkbox"].futurico, input[type="radio"].futurico\').iCheck({
        checkboxClass: \'icheckbox_futurico\',
        radioClass: \'iradio_futurico\'
    });
    $(\'input[type="checkbox"].line, input[type="radio"].line\').iCheck({
        checkboxClass: \'icheckbox_line\',
        radioClass: \'iradio_line\'
    });
    $(\'input[type="checkbox"].minimal, input[type="radio"].minimal\').iCheck({
        checkboxClass: \'icheckbox_minimal-blue\',
        radioClass: \'iradio_minimal-blue\'
    });
    $(\'input[type="checkbox"].polaris, input[type="radio"].polaris\').iCheck({
        checkboxClass: \'icheckbox_polaris\',
        radioClass: \'iradio_polaris\'
    });
    $(\'input[type="checkbox"].square, input[type="radio"].square\').iCheck({
        checkboxClass: \'icheckbox_square\',
        radioClass: \'iradio_square\'
    });',
    ['block' => 'scriptBotton']
);

// time picker
echo $this->Html->scriptBlock(
    '$(\'[data-provide="timepicker"]\').timepicker({
        showMeridian: false,
        minuteStep: 5
    });',
    ['block' => 'scriptBotton']
);

// date range picker (used for datetime pickers)
echo $this->Html->scriptBlock(
    '$(\'[data-provide="daterangepicker"]\').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        timePicker: true,
        drops: "up",
        timePicker12Hour: false,
        timePickerIncrement: 5,
        format: "YYYY-MM-DD HH:mm"
    });',
    ['block' => 'scriptBotton']
);

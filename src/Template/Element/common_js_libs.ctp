<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Configure;

echo $this->Html->css(
    [
        'AdminLTE./plugins/iCheck/all',
        'Qobo/Utils./plugins/daterangepicker/css/daterangepicker.css',
        'AdminLTE./plugins/datepicker/datepicker3',
        'AdminLTE./plugins/timepicker/bootstrap-timepicker.min',
        'AdminLTE./plugins/select2/select2.min',
        'CsvMigrations.fileinput.min',
        'CsvMigrations.style',
        'Qobo/Utils./plugins/datatables/css/dataTables.bootstrap.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style',
        'Qobo/Utils./img/icons/flags/css/flag-icon.css'
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

echo $this->Html->scriptBlock(
    'var tinymce_init_config = ' . json_encode(Configure::read('CsvMigrations.TinyMCE')) . ';',
    ['block' => 'scriptBottom']
);

echo $this->Html->script(
    [
        'CsvMigrations.dom-observer',
        'CsvMigrations.embedded',
        'CsvMigrations.panels',
        'CsvMigrations.fileinput.min',
        'CsvMigrations.fileinput-load',
        'CsvMigrations.jquery.dynamicSelect',
        'CsvMigrations.jquery.dynamicSelectInit',
        'AdminLTE./plugins/iCheck/icheck.min',
        'CsvMigrations.icheck.init',
        'AdminLTE./plugins/daterangepicker/moment.min',
        'Qobo/Utils./plugins/daterangepicker/js/daterangepicker.js',
        'CsvMigrations.datetimepicker.init',
        'AdminLTE./plugins/datepicker/bootstrap-datepicker',
        'CsvMigrations.datepicker.init',
        'AdminLTE./plugins/timepicker/bootstrap-timepicker.min',
        'Qobo/Utils./plugins/datatables/datatables.min',
        'Qobo/Utils./plugins/datatables/js/dataTables.bootstrap.min',
        'CsvMigrations.timepicker.init',
        'AdminLTE./plugins/select2/select2.full.min',
        'CsvMigrations.select2.init',
        'CsvMigrations.plugin',
        'Qobo/Utils./plugins/tinymce/tinymce.min',
        'CsvMigrations.tinymce.init',
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

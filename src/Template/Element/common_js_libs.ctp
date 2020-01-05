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
        'CsvMigrations.style',
        'Qobo/Utils./plugins/datatables/css/dataTables.bootstrap.min',
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



echo $this->Html->scriptBlock(
    'var tinymce_init_config = ' . json_encode(Configure::read('CsvMigrations.TinyMCE')) . ';',
    ['block' => 'scriptBottom']
);

echo $this->Html->script(
    [
        'CsvMigrations.dom-observer',
        'CsvMigrations.embedded',
        'CsvMigrations.panels',
        'CsvMigrations.jquery.dynamicSelect',
        'CsvMigrations.jquery.dynamicSelectInit',
        'AdminLTE./plugins/iCheck/icheck.min',
        'CsvMigrations.icheck.init',
        'Qobo/Utils./plugins/datatables/datatables.min',
        'Qobo/Utils./plugins/datatables/js/dataTables.bootstrap.min',
        'CsvMigrations.plugin',
        'Qobo/Utils./plugins/tinymce/tinymce.min',
        'CsvMigrations.tinymce.init',
    ],
    [
        'block' => 'scriptBottom'
    ]
);

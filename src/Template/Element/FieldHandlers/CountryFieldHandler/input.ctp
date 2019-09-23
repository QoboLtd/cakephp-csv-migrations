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
        'AdminLTE./bower_components/select2/dist/css/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style',
        'Qobo/Utils./img/icons/flags/css/flag-icon.css'
    ],
    [
        'block' => 'css'
    ]
);

$attributes = isset($attributes) ? $attributes : [];

$attributes += [
    'type' => 'select',
    'label' => $label ?: false,
    'options' => $options,
    'class' => 'form-control select2 ' . $extraClasses,
    'required' => (bool)$required,
    'value' => $value
];

echo $this->Form->control($name, $attributes);

echo $this->Html->script(
    [
        'AdminLTE./bower_components/select2/dist/js/select2.full.min',
        'CsvMigrations.select2.init',
        'Qobo/Utils.select2.init',
    ],
    [
        'block' => 'scriptBottom'
    ]
);

echo $this->Html->scriptBlock(
    '$(document).ready(function() {
        csv_migrations_select2.setup(' . json_encode(
            array_merge(
                Configure::read('CsvMigrations.select2')
            )
        ) . ');
    });',
    ['block' => 'scriptBottom']
);

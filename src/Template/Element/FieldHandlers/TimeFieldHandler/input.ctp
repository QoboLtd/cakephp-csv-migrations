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

echo $this->Html->css(
    [
        'Qobo/Utils./plugins/daterangepicker/css/daterangepicker',
        'AdminLTE./bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min',
        'AdminLTE./plugins/timepicker/bootstrap-timepicker.min'
    ],
    [
        'block' => 'css'
    ]
);

$attributes = isset($attributes) ? $attributes : [];

$attributes += [
    'type' => 'text',
    'label' => $label,
    'data-provide' => 'timepicker',
    'autocomplete' => 'off',
    'required' => (bool)$required,
    'value' => $value,
    'templates' => [
        'input' => '<div class="input-group bootstrap-timepicker timepicker">
            <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
            </div>
            <input type="{{type}}" name="{{name}}"{{attrs}}/>
        </div>'
    ]
];

if (!empty($minuteStep)) {
    $attributes += ['data-minute-step' => $minuteStep];
}

echo $this->Form->control($name, $attributes);

echo $this->Html->script(
    [
        'CsvMigrations.dom-observer',
        'AdminLTE./bower_components/moment/min/moment.min',
        'Qobo/Utils./plugins/daterangepicker/js/daterangepicker',
        'CsvMigrations.datetimepicker.init',
        'AdminLTE./bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min',
        'CsvMigrations.datepicker.init',
        'AdminLTE./plugins/timepicker/bootstrap-timepicker.min',
        'CsvMigrations.timepicker.init'
    ],
    [
        'block' => 'scriptBottom'
    ]
);

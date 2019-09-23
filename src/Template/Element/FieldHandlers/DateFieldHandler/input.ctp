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

$attributes = isset($attributes) ? $attributes : [];

$attributes += [
    'type' => 'text',
    'label' => $label,
    'data-provide' => 'datetimepicker',
    'autocomplete' => 'off',
    'required' => (bool)$required,
    'value' => $value,
    'data-time-picker' => 'false',
    'templates' => [
        'input' => '<div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
            </div>
            <input type="{{type}}" name="{{name}}"{{attrs}}/>
        </div>'
    ]
];

echo $this->Form->control($name, $attributes);

echo $this->Html->script(
    [
        'CsvMigrations.dom-observer',
        'AdminLTE./bower_components/moment/min/moment.min',
        'Qobo/Utils./plugins/daterangepicker/js/daterangepicker',
        'AdminLTE./bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min',
        'CsvMigrations.datepicker.init',
    ],
    [
        'block' => 'scriptBottom'
    ]
);

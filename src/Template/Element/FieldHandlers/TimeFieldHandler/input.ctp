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

echo $this->Form->input($name, $attributes);

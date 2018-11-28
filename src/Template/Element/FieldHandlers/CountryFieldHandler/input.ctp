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

$this->Html->script(
    [
        'AdminLTE./plugins/select2/select2.full.min',
        'Qobo/Utils.select2.init',
        'RolesCapabilities.switch-target',
        'RolesCapabilities.permissions',
    ],
    ['block' => 'scriptBottom']
);

$this->Html->css(
    [
        'AdminLTE./plugins/select2/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style'
    ],
    ['block' => 'css']
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
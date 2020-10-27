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
        'CsvMigrations.jquery.inputmask.min',
        'CsvMigrations.inputmask.min',
        'CsvMigrations.inputmask.init',
    ],
    [
        'block' => 'scriptBottom'
    ]
);

$attributes = isset($attributes) ? $attributes : [];
$inputmask = isset($attributes['inputmask']) ? $attributes['inputmask'] : '';
unset($attributes['inputmask']);

$attributes += [
    'type' => $type,
    'label' => $label,
    'required' => (bool)$required,
    'value' => $value,
    'placeholder' => $placeholder,
    'help' => $help,
    'data-inputmask' => trim(json_encode($inputmask), '{}'),
];

echo $this->Form->control($name, $attributes);



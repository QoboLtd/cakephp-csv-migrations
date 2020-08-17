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
      'CsvMigrations.classic.min'
  ],
  [
      'block' => 'css'
  ]
);

$this->Html->script(
    [
        'CsvMigrations.pickr.min',
        'CsvMigrations.pickr.init',
    ],
    [
        'block' => 'scriptBottom'
    ]
);


$attributes = isset($attributes) ? $attributes : [];

$attributes += [
    'type' => $type,
    'label' => $label,
    'required' => (bool)$required,
    'value' => $value,
    'placeholder' => $placeholder,
    'help' => $help,
    'class' => 'form-control pickr ' . $extraClasses,
    'templates' => [
      'input' => '<div class="input-group ">
                    <div class="input-group-addon">
                      <i style="background-color: white; display: inline-block; height: 16px; vertical-align: text-top; width: 16px;"></i>
                    </div>
                    <input type="{{type}}" name="{{name}}"{{attrs}} class="input-group-addon"/>
                  </div>',
  ]
];

echo $this->Form->control($name, $attributes);
?>


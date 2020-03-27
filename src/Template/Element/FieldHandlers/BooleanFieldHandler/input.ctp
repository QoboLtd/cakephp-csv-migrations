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

$help_tooltip = empty($help) ? '' : '<span class="help-tip"><p>' . $help . '</p></span>';

$label = $this->Form->label($name, $label) . $help_tooltip;


$attributes += [
    'type' => $type,
    'class' => 'square' . ( (isset($extraClasses) && !empty($extraClasses)) ? ' ' . $extraClasses : null ),
    'required' => (bool)$required,
    'checked' => (bool)$value,
    'label' => false,
    'templates' => [
        'inputContainer' => '<div class="form-group input {{required}}">' .
            $label . '<div class="clearfix"></div>{{content}}</div>',
        'inputContainerError' => '<div class="form-group input {{required}} has-error">' .
            $label . '<div class="clearfix"></div>{{content}}{{error}}</div>'
    ]
];

echo $this->Form->control($name, $attributes);

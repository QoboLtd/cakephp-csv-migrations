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

$label = $this->Form->label($name, $label);

$attributes += [
    'type' => $type,
    'class' => 'square' . ( (isset($extraClasses) && !empty($extraClasses)) ? ' ' . $extraClasses : null ),
    'required' => (bool)$required,
    'checked' => (bool)$value,
    'label' => false,
    'templates' => [
        'inputContainer' => '<div class="{{required}}">' . $label . '<div class="clearfix"></div>{{content}}</div>'
    ]
];

echo $this->Form->input($name, $attributes);

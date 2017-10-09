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

$attributes += [
    'type' => $type,
    'label' => $label,
    'required' => (bool)$required,
    'value' => $value,
    'step' => $step,
    'max' => $max
];

echo $this->Form->input($name, $attributes);

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

$levels = 0;
// get nesting level based on dot notation
foreach ($optionValues as $k => $v) {
    $count = substr_count($k, '.');
    if ($count <= $levels) {
        continue;
    }
    $levels = $count;
}

// get selectors
$selectors = [];
for ($i = 0; $i <= $levels; $i++) {
    $selectors[] = '[data-target="' . 'dynamic-select-' . $field . '_' . $i . '"]';
}

// edit mode
if (!empty($value)) {
    $value = explode('.', $value);
    $count = count($value);
}

// echo inputs
for ($i = 0; $i <= $levels; $i++) {
    $options = [
        'type' => $type,
        'required' => (bool)$required
    ];
    $options['data-target'] = 'dynamic-select-' . $field . '_' . $i;
    // top (first) level input
    if (0 === $i) {
        $options['data-type'] = 'dynamic-select';
        $options['data-structure'] = json_encode($structure);
        $options['data-option-values'] = json_encode(array_flip($optionValues));
        $options['data-selectors'] = json_encode($selectors);
        $options['data-hide-next'] = true;
        $options['data-previous-default-value'] = true;
    } else {
        $options['label'] = false;
    }
    // edit mode
    if (!empty($value) && ($i + 1) <= $count) {
        $options['data-value'] = implode('.', array_slice($value, 0, $i + 1));
    }

    echo $this->Form->input($name, array_merge($options, $attributes));
}

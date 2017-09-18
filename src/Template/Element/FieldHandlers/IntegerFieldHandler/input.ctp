<?php
$attributes += [
    'type' => $type,
    'label' => $label,
    'required' => (bool)$required,
    'value' => $value,
    'step' => $step,
    'max' => $max
];

echo $this->Form->input($name, $attributes);

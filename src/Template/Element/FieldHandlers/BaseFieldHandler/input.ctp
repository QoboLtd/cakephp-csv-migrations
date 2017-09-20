<?php
$attributes += [
    'type' => $type,
    'label' => $label,
    'required' => (bool)$required,
    'value' => $value
];

echo $this->Form->input($name, $attributes);

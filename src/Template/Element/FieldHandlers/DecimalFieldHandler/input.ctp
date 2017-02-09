<?php
echo $this->Form->input($name, [
    'type' => $type,
    'label' => $label,
    'required' => (bool)$required,
    'value' => $value,
    'step' => $step,
    'max' => $max
]);

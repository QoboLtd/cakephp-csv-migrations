<?php
$attributes += [
    'class' => 'form-control' . ((isset($extraClasses) && !empty($extraClasses)) ? ' ' . $extraClasses : null),
    'required' => (bool)$required,
    'value' => $value
];
?>
<div class="form-group<?= $required ? ' required' : '' ?>">
    <?= $label ? $this->Form->label($name, $label) : ''; ?>
    <?= $this->Form->select($name, $options, $attributes); ?>
</div>

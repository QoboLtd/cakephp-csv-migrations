<div class="form-group<?= $required ? ' required' : '' ?>">
    <?= $label ? $this->Form->label($name, $label) : ''; ?>
    <?= $this->Form->select($name, $options, [
        'class' => 'form-control',
        'required' => (bool)$required,
        'value' => $value
    ]); ?>
</div>
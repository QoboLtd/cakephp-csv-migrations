<div class="form-group">
<?php if ($options['label']) : ?>
    <?= $this->Form->label($field); ?>
<?php endif; ?>
<?= $this->Form->select($field, $selOptions, $options); ?>
</div>
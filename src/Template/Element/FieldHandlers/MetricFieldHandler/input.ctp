<?= $this->Form->label($field, $label); ?>
<div class="row">
<?php foreach ($inputs as $input) : ?>
    <div class="col-xs-6 col-lg-4"><?= $input ?></div>
<?php endforeach; ?>
</div>
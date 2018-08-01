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

$attributes = isset($attributes) ? $attributes : [];

$id = 'select2-' . uniqid();

$attributes += [
    'id' => $id,
    'type' => 'select',
    'label' => false,
    'options' => $options,
    'data-type' => 'select2',
    'data-backend' => "off",
    'class' => 'select2 form-control ' . $extraClasses,
    'required' => (bool)$required,
    'value' => $value,
    'escape' => true,
];
?>
<div class="form-group <?= $required ? 'required' : '' ?> <?= $this->Form->isFieldError($name) ? 'has-error' : '' ?>">
    <?= $this->Form->label($name, $label, ['class' => 'control-label']) ?>
    <div class="input-group">
        <div class="input-group-addon">
            <span class="fa fa-list"></span>
        </div>
        <?= $this->Form->input($name, $attributes);
        ?>
    </div>
    <?php echo $this->Form->error($name) ?>
</div>

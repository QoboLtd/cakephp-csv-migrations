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
$hasError = false;
foreach (array_keys($inputs) as $fieldName) {
    if ($this->Form->isFieldError($fieldName)) {
        $hasError = true;
        break;
    }
}

?>
<div class="form-group <?= $required ? 'required' : '' ?> <?= $hasError ? 'has-error' : '' ?>">
    <?= $this->Form->label($field, $label); ?>
    <div class="row combined-field">
    <?php 
        $i = true;
        foreach ($inputs as $input) : 
            $help = '';
            if ($i) {
                $help = $this->Html->help($help);
                $i = false;
            }
    ?>
    
        <div <?= $help ?> class="col-xs-6 col-md-6 col-lg-5"><?= $input ?></div>
    <?php endforeach; ?>
    </div>
</div>

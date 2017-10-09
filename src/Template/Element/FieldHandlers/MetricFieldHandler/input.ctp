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
?>
<div class="form-group<?= $required ? ' required' : '' ?>">
    <?= $this->Form->label($field, $label); ?>
    <div class="row">
    <?php foreach ($inputs as $input) : ?>
        <div class="col-xs-6 col-lg-4"><?= $input ?></div>
    <?php endforeach; ?>
    </div>
</div>

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

$attributes += [
    'class' => 'form-control' . ( (isset($extraClasses) && !empty($extraClasses)) ? ' ' . $extraClasses : null ),
    'required' => (bool)$required,
    'value' => $value
];
?>
<div class="form-group<?= $required ? ' required' : '' ?>">
    <?= $label ? $this->Form->label($name, $label) : ''; ?>
    <?= $this->Form->select($name, $options, $attributes); ?>
</div>

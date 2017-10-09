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
    'empty' => true,
    'value' => $value,
    'options' => $options,
    'label' => false,
    'id' => $field,
    'type' => $type,
    'title' => $title,
    'data-type' => 'select2',
    'data-display-field' => $relatedProperties['displayField'],
    'escape' => false,
    'autocomplete' => 'off',
    'required' => (bool)$required,
    'data-url' => $this->Url->build([
        'prefix' => 'api',
        'plugin' => $relatedProperties['plugin'],
        'controller' => $relatedProperties['controller'],
        'action' => 'lookup.json'
    ])
];
?>
<div class="form-group<?= $required ? ' required' : '' ?>">
    <?= $this->Form->label($name, $label, ['class' => 'control-label']) ?>
    <div class="input-group select2-bootstrap-prepend select2-bootstrap-append">
        <span class="input-group-addon" title="<?= $relatedProperties['controller'] ?>">
            <span class="fa fa-<?= $icon ?>"></span>
        </span>
        <?= $this->Form->input($name, $attributes);
        ?>
        <?php if ($embedded) : ?>
            <div class="input-group-btn">
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#<?= $field ?>_modal">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

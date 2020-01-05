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

use Cake\Core\Configure;

$attributes = isset($attributes) ? $attributes : [];

echo $this->Html->css(
    [
        'AdminLTE./bower_components/select2/dist/css/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style',
        'Qobo/Utils./img/icons/flags/css/flag-icon.css'
    ],
    [
        'block' => 'css'
    ]
);

$attributes += [
    'empty' => true,
    'value' => $value,
    'options' => $options,
    'label' => false,
    'error' => false,
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
<div class="form-group <?= $required ? 'required' : '' ?> <?= $this->Form->isFieldError($name) ? 'has-error' : '' ?>">
    <?= $this->Form->label($name, $label, ['class' => 'control-label']) ?>
    <div class="input-group">
        <div class="input-group-addon" title="<?= $relatedProperties['controller'] ?>">
            <span class="fa fa-<?= $icon ?>"></span>
        </div>
        <?= $this->Form->control($name, $attributes);
        ?>
    <?php if ($embedded) : ?>
        <div class="input-group-btn">
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#<?= $field ?>_modal">
                <i class="fa fa-plus" aria-hidden="true"></i>
            </button>
        </div>
    <?php endif; ?>
    </div>
    <?php echo $this->Form->error($name) ?>
</div>

<?php

    echo $this->Html->script(
        [
            'AdminLTE./bower_components/select2/dist/js/select2.full.min',
            'CsvMigrations.select2.init',
            'Qobo/Utils.select2.init',
        ],
        [
            'block' => 'scriptBottom'
        ]
    );

    echo $this->Html->scriptBlock(
        '$(document).ready(function() {
            csv_migrations_select2.setup(' . json_encode(
                array_merge(
                    Configure::read('CsvMigrations.select2'),
                    Configure::read('CsvMigrations.api')
                )
            ) . ');
        });',
        ['block' => 'scriptBottom']
    );

?>

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
?>
<div class="btn-group btn-group-sm" role="group">
<?php if (Configure::read('CsvMigrations.batch.active')) : ?>
    <?= $this->Form->button('<i class="fa fa-bars"></i> Batch', [
        'id' => 'batch-button',
        'type' => 'button',
        'class' => 'btn btn-default dropdown-toggle',
        'data-toggle' => 'dropdown',
        'aria-haspopup' => 'true',
        'aria-expanded' => 'false',
        'disabled' => true
    ]) ?>
    <ul class="dropdown-menu">
        <li>
        <?= $this->Html->link(
            '<i class="fa fa-pencil"></i> ' . __('Edit'),
            ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch'],
            [
                'id' => 'batch-edit-button',
                'data-batch' => true,
                'data-batch-url' => $this->Url->build([
                    'plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch', 'edit'
                ]),
                'escape' => false
            ]
        ) ?></li>
        <li>
        <?= $this->Html->link(
            '<i class="fa fa-trash"></i> ' . __('Delete'),
            ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch'],
            [
                'id' => 'batch-delete-button',
                'data-batch' => true,
                'data-batch-url' => $this->Url->build([
                    'plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch', 'delete'
                ]),
                'data-batch-confirm' => 'Are you sure you want to delete the selected records?',
                'escape' => false
            ]
        ) ?></li>
    </ul>
<?php endif; ?>
    <?= $this->Html->link(
        '<i class="fa fa-upload"></i> ' . __('Import'),
        ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'import'],
        ['escape' => false, 'title' => __('Import Data'), 'class' => 'btn btn-default']
    ) ?>
    <?= $this->Html->link(
        '<i class="fa fa-plus"></i> ' . __('Add'),
        ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'add'],
        ['escape' => false, 'title' => __('Add'), 'class' => 'btn btn-default']
    ) ?>
</div>
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

use CsvMigrations\Model\Table\ImportsTable;

$statusLabels = [
    ImportsTable::STATUS_IN_PROGRESS => 'primary',
    ImportsTable::STATUS_COMPLETED => 'success',
    ImportsTable::STATUS_PENDING => 'warning',
    ImportsTable::STATUS_FAIL => 'error'
];
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Import data for') ?> <?= $this->name ?></h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <?= __('File upload') ?>
                    </h3>
                </div>
                <div class="box-body">
                <?= $this->Form->create($import, ['type' => 'file']) ?>
                    <div class="form-group">
                        <?= $this->Form->file('file', ['required' => true]) ?>
                        <p class="help-block"><?= __('Supported file types: .csv') ?></p>
                    </div>
                    <?= $this->Form->button(__('Submit'), ['type' => 'submit', 'class' => 'btn btn-primary']) ?>
                <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
    <?php if (!$existingImports->isEmpty()) : ?>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <?= __('Existing imports') ?>
                    </h3>
                </div>
                <div class="box-body">
                    <table id="progress-table" class="table table-hover table-condensed table-vertical-align" width="100%">
                        <thead>
                            <tr>
                                <th><?= __('Filename'); ?></th>
                                <th><?= __('Status'); ?></th>
                                <th><?= __('Attempts'); ?></th>
                                <th><?= __('Last attempt'); ?></th>
                                <th class="actions"><?= __('Actions'); ?></th>
                            </tr>
                            <?php foreach ($existingImports as $existingImport) : ?>
                                <tr>
                                    <td><?= basename($existingImport->get('filename')) ?></td>
                                    <td>
                                        <span class="label label-<?= $statusLabels[$existingImport->get('status')] ?>">
                                            <?= $existingImport->get('status') ?>
                                        </span>
                                    </td>
                                    <td><?= $existingImport->get('attempts') ?></td>
                                    <td><?php
                                    if ($existingImport->get('attempted_date')) {
                                        echo $existingImport->attempted_date->i18nFormat('yyyy-MM-dd HH:mm');
                                    } ?></td>
                                    <td class="actions">
                                        <div class="btn-group btn-group-xs" role="group">
                                        <?= $this->Html->link(
                                            '<i class="fa fa-eye"></i>',
                                            ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'import', $existingImport->id],
                                            ['title' => __('View'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                                        ); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

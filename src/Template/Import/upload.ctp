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

use Cake\Utility\Inflector;
use CsvMigrations\Model\Table\ImportsTable;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

$statusLabels = [
    ImportsTable::STATUS_IN_PROGRESS => 'primary',
    ImportsTable::STATUS_COMPLETED => 'success',
    ImportsTable::STATUS_PENDING => 'warning',
    ImportsTable::STATUS_FAIL => 'error'
];
$options = [
    'title' => null,
    'entity' => null,
    'fields' => [],
];

// generate title
if (!$options['title']) {
    $config = (new ModuleConfig(ConfigType::MODULE(), $this->name))->parse();
    $options['title'] = $this->Html->link(
        isset($config->table->alias) ? $config->table->alias : Inflector::humanize(Inflector::underscore($this->name)),
        ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'index']
    );
    $options['title'] .= ' &raquo; ';
    $options['title'] .= __d('Qobo/CsvMigrations', 'Import Data');
}
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= $options['title'] ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <?= __d('Qobo/CsvMigrations', 'File upload') ?>
                    </h3>
                </div>
                <div class="box-body">
                <?= $this->Form->create($import, ['type' => 'file']) ?>
                    <div class="form-group">
                        <?= $this->Form->file('file', ['required' => true]) ?>
                        <p class="help-block"><?= __d('Qobo/CsvMigrations', 'Supported file types: .csv') ?></p>
                    </div>
                    <?= $this->Form->button(__d('Qobo/CsvMigrations', 'Submit'), ['type' => 'submit', 'class' => 'btn btn-primary']) ?>
                <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
    <?php if (!$existingImports->isEmpty()) : ?>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <?= __d('Qobo/CsvMigrations', 'Existing imports') ?>
                    </h3>
                </div>
                <div class="box-body">
                    <table id="progress-table" class="table table-hover table-condensed table-vertical-align" width="100%">
                        <thead>
                            <tr>
                                <th><?= __d('Qobo/CsvMigrations', 'Filename'); ?></th>
                                <th><?= __d('Qobo/CsvMigrations', 'Status'); ?></th>
                                <th><?= __d('Qobo/CsvMigrations', 'Attempts'); ?></th>
                                <th><?= __d('Qobo/CsvMigrations', 'Last attempt'); ?></th>
                                <th class="actions"><?= __d('Qobo/CsvMigrations', 'Actions'); ?></th>
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
                                            ['title' => __d('Qobo/CsvMigrations', 'View'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
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

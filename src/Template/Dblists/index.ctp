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
use CsvMigrations\FieldHandlers\Renderer\DateTimeRenderer;

$renderer = new DateTimeRenderer($this);

echo $this->Html->css('AdminLTE./plugins/datatables/dataTables.bootstrap', ['block' => 'css']);
echo $this->Html->script(
    [
        'AdminLTE./plugins/datatables/jquery.dataTables.min',
        'AdminLTE./plugins/datatables/dataTables.bootstrap.min'
    ],
    [
        'block' => 'scriptBottom'
    ]
);
echo $this->Html->scriptBlock(
    '$(".table-datatable").DataTable({
        stateSave: true,
        stateDuration: ' . (int)(Configure::read('Session.timeout') * 60) . '
    });',
    ['block' => 'scriptBottom']
);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __d('CsvMigrations', 'Database Lists') ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                    <?= $this->element('CsvMigrations.Menu/dblists_index_top', ['user' => $user]) ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            <table class="table table-hover table-condensed table-vertical-align table-datatable">
                <thead>
                    <tr>
                        <th><?= __('Name'); ?></th>
                        <th><?= __('Created'); ?></th>
                        <th><?= __('Modified'); ?></th>
                        <th class="actions"><?= __d('CsvMigrations', 'Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dblists as $dblist) : ?>
                    <tr>
                        <td><?= h($dblist->name) ?></td>
                        <td><?= $renderer->renderValue($dblist->created) ?></td>
                        <td><?= $renderer->renderValue($dblist->modified) ?></td>
                        <td class="actions">
                            <?= $this->element('CsvMigrations.Menu/dblists_index_actions', [
                                'entity' => $dblist
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

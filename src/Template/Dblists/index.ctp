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

echo $this->Html->css('Qobo/Utils./plugins/datatables/css/dataTables.bootstrap.min', ['block' => 'css']);

echo $this->Html->script(
    [
        'Qobo/Utils./plugins/datatables/datatables.min',
        'Qobo/Utils./plugins/datatables/js/dataTables.bootstrap.min',
    ],
    ['block' => 'scriptBottom']
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
            <h4><?= __('Database Lists') ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                    <?php
                    $url = ['plugin' => 'CsvMigrations', 'controller' => 'Dblists', 'action' => 'add'];
                    echo $this->Html->link('<i class="fa fa-plus"></i> ' . __('Add'), $url, [
                        'title' => __d('CsvMigrations', 'Add'), 'escape' => false, 'class' => 'btn btn-default'
                    ]);
                    ?>
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
                        <th class="actions"><?= __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dblists as $dblist) : ?>
                    <tr>
                        <td><?= h($dblist->name) ?></td>
                        <td><?= $renderer->renderValue($dblist->created) ?></td>
                        <td><?= $renderer->renderValue($dblist->modified) ?></td>
                        <td class="actions">
                            <div class="btn-group btn-group-xs" role="group">
                            <?php
                            $url = [
                                'plugin' => 'CsvMigrations',
                                'controller' => 'DblistItems',
                                'action' => 'index',
                                $dblist->id
                            ];
                            echo $this->Html->link('<i class="fa fa-list-alt"></i>', $url, [
                                'title' => __('View'), 'class' => 'btn btn-default', 'escape' => false
                            ]);

                            $url = [
                                'plugin' => 'CsvMigrations',
                                'controller' => 'Dblists',
                                'action' => 'edit',
                                $dblist->id
                            ];
                            echo $this->Html->link('<i class="fa fa-pencil"></i>', $url, [
                                'title' => __('Edit'), 'class' => 'btn btn-default', 'escape' => false
                            ]);

                            $url = [
                                'plugin' => 'CsvMigrations',
                                'controller' => 'Dblists',
                                'action' => 'delete',
                                $dblist->id
                            ];
                            echo $this->Form->postLink('<i class="fa fa-trash"></i>', $url, [
                                'title' => __('Delete'),
                                'class' => 'btn btn-default btn-sm',
                                'escape' => false,
                                'confirm' => __d('CsvMigrations', 'Are you sure you want to delete {0}?', $dblist->name)
                            ]);
                            ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

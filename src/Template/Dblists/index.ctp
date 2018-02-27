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
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$factory = new FieldHandlerFactory($this);

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
                    <?= $this->Html->link(
                        '<i class="fa fa-plus"></i> ' . __('Add'),
                        ['plugin' => 'CsvMigrations', 'controller' => 'Dblists', 'action' => 'add'],
                        ['title' => __d('CsvMigrations', 'Add'), 'escape' => false, 'class' => 'btn btn-default']
                    ); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-primary">
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
                    <?php foreach ($entities as $entity) : ?>
                    <tr>
                        <td><?= h($entity->get('name')) ?></td>
                        <td><?= $factory->renderValue('Dblists', 'created', $entity->get('created'), ['fieldDefinitions' => ['type' => 'datetime']]);?></td>
                        <td><?= $factory->renderValue('Dblists', 'modified', $entity->get('modified'), ['fieldDefinitions' => ['type' => 'datetime']]);?></td>
                        <td class="actions">
                            <div class="btn-group btn-group-xs" role="group">
                            <?php
                            echo $this->Html->link(
                                '<i class="fa fa-list-alt"></i>',
                                [
                                    'plugin' => 'CsvMigrations',
                                    'controller' => 'DblistItems',
                                    'action' => 'index',
                                    $entity->get('id')
                                ],
                                ['title' => __('View'), 'class' => 'btn btn-default', 'escape' => false]
                            );

                            echo $this->Html->link(
                                '<i class="fa fa-pencil"></i>',
                                [
                                    'plugin' => 'CsvMigrations',
                                    'controller' => 'Dblists',
                                    'action' => 'edit',
                                    $entity->get('id')
                                ],
                                ['title' => __('Edit'), 'class' => 'btn btn-default', 'escape' => false]
                            );

                            echo $this->Form->postLink(
                                '<i class="fa fa-trash"></i>',
                                [
                                    'plugin' => 'CsvMigrations',
                                    'controller' => 'Dblists',
                                    'action' => 'delete',
                                    $entity->get('id')
                                ],
                                [
                                    'title' => __('Delete'),
                                    'class' => 'btn btn-default btn-sm',
                                    'escape' => false,
                                    'confirm' => __('Are you sure you want to delete {0}?', $entity->get('name'))
                                ]
                            );
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

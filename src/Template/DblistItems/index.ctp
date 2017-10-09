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

use CsvMigrations\FieldHandlers\Renderer\DateTimeRenderer;

$renderer = new DateTimeRenderer($this);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
        <h4>
            <?= __d('CsvMigrations', 'Database List Items') ?>
            <small>
                <?= __('for') ?>
                <?= $this->Html->link($list->get('name'), ['controller' => 'Dblists', 'action' => 'index']) ?>
            </small>
        </h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                    <?= $this->element('CsvMigrations.Menu/dblist_items_index_top', ['entity' => $list, 'user' => $user]) ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body table-responsive">
            <table class="table table-hover table-condensed table-vertical-align">
                <thead>
                    <tr>
                        <th><?= __d('CsvMigrations', 'Name'); ?></th>
                        <th><?= __d('CsvMigrations', 'Value'); ?></th>
                        <th><?= __d('CsvMigrations', 'Created'); ?></th>
                        <th><?= __d('CsvMigrations', 'Modified'); ?></th>
                        <th class="actions"><?= __d('CsvMigrations', 'Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tree as $entity) : ?>
                    <tr class="<?= !($entity->get('active')) ? 'warning' : ''; ?>">
                        <td><?= $entity->get('spacer')?></td>
                        <td><?= $entity->get('value') ?></td>
                        <td><?= $renderer->renderValue($entity->created) ?></td>
                        <td><?= $renderer->renderValue($entity->modified) ?></td>
                        <td class="actions">
                            <?= $this->element('CsvMigrations.Menu/dblist_items_index_actions', [
                                'entity' => $entity
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

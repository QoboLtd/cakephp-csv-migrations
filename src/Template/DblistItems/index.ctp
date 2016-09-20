<?php
$this->extend('QoboAdminPanel./Common/panel-wrapper');
$this->assign('title', 'List items of ' . $list->get('name'));
$this->assign('panel-title', __d('QoboAdminPanel', 'Details'));
?>
<table class="table">
    <thead>
        <tr>
            <th><?= __d('CsvMigrations', 'Name'); ?></th>
            <th class="actions"><?= __('Actions'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tree as $entity): ?>
        <tr class="<?= !($entity->get('active')) ? 'warning' : ''; ?>">
            <td><?= $entity->get('spacer') ?></td>
            <td class="actions">
                <?= $this->Form->postLink('', ['action' => 'move_node', $entity->get('id'), 'up'], ['title' => __('Move up'), 'class' => 'btn btn-default glyphicon glyphicon-arrow-up']) ?>
                <?= $this->Form->postLink('', ['action' => 'move_node', $entity->get('id'), 'down'], ['title' => __('Move down'), 'class' => 'btn btn-default glyphicon glyphicon-arrow-down']) ?>
                <?= $this->Html->link('', ['action' => 'edit', $entity->get('id')], ['title' => __('Edit'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']) ?>
                <?= $this->Form->postLink('', ['action' => 'delete', $entity->get('id')], ['confirm' => __('Are you sure you want to delete # {0}?', $entity->get('id')), 'title' => __('Delete'), 'class' => 'btn btn-default glyphicon glyphicon-trash']) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
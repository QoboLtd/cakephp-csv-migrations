<?php
$this->extend('QoboAdminPanel./Common/panel-wrapper');
$this->assign('title', 'List items of ' . $list->get('name'));
$this->assign('panel-title', __d('QoboAdminPanel', 'Details'));
?>
<table class="table table-striped" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th><?= __d('CsvMigrations', 'Name'); ?></th>
            <th class="actions"><?= __('Actions'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($dblistItems as $id => $name): ?>
        <tr>
            <td><?= $name ?></td>
            <td class="actions">
                <?= $this->Form->postLink('', ['action' => 'move_node', $id, 'up'], ['title' => __('Move up'), 'class' => 'btn btn-default glyphicon glyphicon-arrow-up']) ?>
                <?= $this->Form->postLink('', ['action' => 'move_node', $id, 'down'], ['title' => __('Move down'), 'class' => 'btn btn-default glyphicon glyphicon-arrow-down']) ?>
                <?= $this->Html->link('', ['action' => 'edit', $id], ['title' => __('Edit'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']) ?>
                <?= $this->Form->postLink('', ['action' => 'delete', $id], ['confirm' => __('Are you sure you want to delete # {0}?', $id), 'title' => __('Delete'), 'class' => 'btn btn-default glyphicon glyphicon-trash']) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
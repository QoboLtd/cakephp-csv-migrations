<?php
$this->extend('QoboAdminPanel./Common/panel-wrapper');
$this->assign('panel-title', __('Details'));
?>
<table class="table table-striped" cellpadding="0" cellspacing="0">
    <tr>
        <td><?= __('Id') ?></td>
        <td><?= h($dbListItem->id) ?></td>
    </tr>
    <tr>
        <td><?= __('Db List') ?></td>
        <td><?= $dbListItem->has('db_list') ? $this->Html->link($dbListItem->db_list->name, ['controller' => 'DbLists', 'action' => 'view', $dbListItem->db_list->id]) : '' ?></td>
    </tr>
    <tr>
        <td><?= __('Name') ?></td>
        <td><?= h($dbListItem->name) ?></td>
    </tr>
    <tr>
        <td><?= __('Value') ?></td>
        <td><?= h($dbListItem->value) ?></td>
    </tr>
    <tr>
        <td><?= __('Created') ?></td>
        <td><?= h($dbListItem->created) ?></td>
    </tr>
    <tr>
        <td><?= __('Modified') ?></td>
        <td><?= h($dbListItem->modified) ?></td>
    </tr>
</table>


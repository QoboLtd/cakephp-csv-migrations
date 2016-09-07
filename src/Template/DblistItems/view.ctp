<?php
$this->extend('QoboAdminPanel./Common/panel-wrapper');
$this->assign('panel-title', __('Details'));
?>
<table class="table table-striped" cellpadding="0" cellspacing="0">
    <tr>
        <td><?= __('Id') ?></td>
        <td><?= h($dblistItem->id) ?></td>
    </tr>
    <tr>
        <td><?= __('Db List') ?></td>
        <td><?= $dblistItem->has('db_list') ? $this->Html->link($dblistItem->db_list->name, ['controller' => 'Dblist', 'action' => 'view', $dblistItem->db_list->id]) : '' ?></td>
    </tr>
    <tr>
        <td><?= __('Name') ?></td>
        <td><?= h($dblistItem->name) ?></td>
    </tr>
    <tr>
        <td><?= __('Value') ?></td>
        <td><?= h($dblistItem->value) ?></td>
    </tr>
    <tr>
        <td><?= __('Created') ?></td>
        <td><?= h($dblistItem->created) ?></td>
    </tr>
    <tr>
        <td><?= __('Modified') ?></td>
        <td><?= h($dblistItem->modified) ?></td>
    </tr>
</table>


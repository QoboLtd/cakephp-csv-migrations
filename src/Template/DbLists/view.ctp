<?php
$this->extend('QoboAdminPanel./Common/panel-wrapper');
$this->assign('panel-title', __('Details'));
?>
<table class="table table-striped" cellpadding="0" cellspacing="0">
    <tr>
        <td><?= __('Name') ?></td>
        <td><?= h($dbList->name) ?></td>
    </tr>
    <tr>
        <td><?= __('Created') ?></td>
        <td><?= h($dbList->created) ?></td>
    </tr>
    <tr>
        <td><?= __('Modified') ?></td>
        <td><?= h($dbList->modified) ?></td>
    </tr>
</table>

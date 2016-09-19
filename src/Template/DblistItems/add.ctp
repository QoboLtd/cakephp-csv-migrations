<?php
$this->extend('QoboAdminPanel./Common/panel-wrapper');
$this->assign('title', __d('CsvMigrations', 'Add item to {0}', $list->get('name')));
$this->assign('panel-title', __d('QoboAdminPanel', 'Details'));
?>
<?= $this->Form->create($dblistItem); ?>
<div class="row">
    <div class="col-xs-12">
        <fieldset>
        <div class="row">
            <div class="col-xs-6">
                <?= $this->Form->input('parent_id', ['options' => $tree, 'escape' => false, 'empty' => true]); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6">
                <?= $this->Form->input('name'); ?>
            </div>
            <div class="col-xs-6">
                <?= $this->Form->input('value'); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6">
                <?= $this->Form->input('active', ['checked' => 'checked']); ?>
            </div>
        </div>
        </fieldset>
        <?= $this->Form->hidden('dblist_id',['value' => $list['id']]); ?>
        <?= $this->Form->button(__("Submit"), ['class' => 'btn btn-primary']); ?>
        <?= $this->Form->end() ?>
    </div>
</div>
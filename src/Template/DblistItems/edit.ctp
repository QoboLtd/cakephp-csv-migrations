<?php
$this->extend('QoboAdminPanel./Common/panel-wrapper');
$this->assign('panel-title', __d('QoboAdminPanel', 'Details'));
?>
<?= $this->Form->create($dblistItem); ?>
<div class="row">
    <div class="col-xs-12">
        <fieldset>
        <div class="row">
            <div class="col-xs-6">
                <?= $this->Form->input('dblist_id', ['options' => $dblists]); ?>
            </div>
            <div class="col-xs-6">
                <?= $this->Form->input('parent_id', ['options' => $tree, 'escape' => true, 'empty' => true]); ?>
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
        </fieldset>
        <?= $this->Form->button(__("Submit"), ['class' => 'btn btn-primary']); ?>
        <?= $this->Form->end() ?>
    </div>
</div>
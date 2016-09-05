<?php
$this->extend('QoboAdminPanel./Common/panel-wrapper');
$this->assign('panel-title', __d('QoboAdminPanel', 'Details'));
?>
<div class="row">
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-6">
                <?= $this->Form->create($dbList); ?>
                <fieldset>
                    <?php
                    echo $this->Form->input('name');
                    ?>
                </fieldset>
                <?= $this->Form->button(__("Submit"), ['class' => 'btn btn-primary']); ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
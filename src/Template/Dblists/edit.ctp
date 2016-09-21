<?php
$this->extend('/Common/panel-wrapper');
$mainTitle = $this->element(
    'top-row',
    ['title' => __d('CsvMigrations', 'Edit database list')]
);
$this->assign('main-title', $mainTitle);
$this->assign('panel-title', __d('CsvMigrations', 'Details'));
?>
<div class="row">
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-6">
                <?= $this->Form->create($dblist); ?>
                <fieldset>
                    <?php
                    echo $this->Form->input('name');
                    ?>
                </fieldset>
                <?= $this->Form->button(__d('CsvMigrations', 'Submit'), ['class' => 'btn btn-primary']); ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
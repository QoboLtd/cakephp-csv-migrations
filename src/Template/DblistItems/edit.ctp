<section class="content-header">
    <h4><?= __d('CsvMigrations', 'Edit Database List Item') ?></h4>
</section>
<section class="content">
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><?= __d('CsvMigrations', 'Details') ?></h3>
        </div>
        <div class="box-body">
        <?= $this->Form->create($dblistItem); ?>
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
                    <?= $this->Form->input('active'); ?>
                </div>
            </div>
        <?= $this->Form->hidden('dblist_id', ['value' => $list['id']]); ?>
        <?= $this->Form->button(__d('CsvMigrations', "Submit"), ['class' => 'btn btn-primary']); ?>
        <?= $this->Form->end() ?>
        </div>
    </div>
</section>

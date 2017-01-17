<section class="content-header">
    <h1><?= __d('CsvMigrations', 'Edit Database List') ?></h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-body">
                    <div class="col-xs-12">
                    <?= $this->Form->create($dblist); ?>
                        <?= $this->Form->input('name') ?>
                        <?= $this->Form->button(__d('CsvMigrations', 'Submit'), ['class' => 'btn btn-primary']); ?>
                    <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __d('CsvMigrations', 'Edit Database List') ?></h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __d('CsvMigrations', 'Details') ?></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                        <?= $this->Form->create($dblist); ?>
                            <?= $this->Form->input('name') ?>
                            <?= $this->Form->button(__d('CsvMigrations', 'Submit'), ['class' => 'btn btn-primary']); ?>
                        <?= $this->Form->end() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

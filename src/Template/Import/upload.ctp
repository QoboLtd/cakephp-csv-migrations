<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Import data for') ?> <?= $this->name ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                    <?= $this->element('CsvMigrations.Menu/index_top', ['user' => $user]) ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <?= __('File upload') ?>
                    </h3>
                </div>
                <div class="box-body">
                <?= $this->Form->create($import, ['type' => 'file']) ?>
                    <div class="form-group">
                        <?= $this->Form->file('file', ['required' => true]) ?>
                        <p class="help-block"><?= __('Supported file types: .csv') ?></p>
                    </div>
                    <?= $this->Form->button(__('Submit'), ['type' => 'submit', 'class' => 'btn btn-primary']) ?>
                <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</section>
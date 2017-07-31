<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Import mapping') ?></h4>
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
                        <?= __('Fields mapping') ?>
                    </h3>
                </div>
                <div class="box-body">
                <?php
                echo $this->Form->create($import);

                foreach ($headers as $header) {
                    echo $this->Form->input($header, [
                        'empty' => true,
                        'type' => 'select',
                        'value' => array_key_exists($header, $fields) ? $fields[$header] : false,
                        'options' => $fields,
                        'class' => 'form-control'
                    ]);
                }
                echo $this->Form->button(__('Submit'), ['type' => 'submit', 'class' => 'btn btn-primary']);
                echo $this->Form->end()
                ?>
                </div>
            </div>
        </div>
    </div>
</section>
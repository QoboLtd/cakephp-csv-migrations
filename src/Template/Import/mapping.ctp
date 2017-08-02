<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Import mapping') ?></h4>
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
                echo $this->Form->create($import, ['class' => 'form-horizontal']);
                foreach ($fields as $field) {
                    echo $this->Form->input('options.' . $field, [
                        'empty' => true,
                        'type' => 'select',
                        'value' => array_key_exists($field, $headers) ? $field : false,
                        'options' => $headers,
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
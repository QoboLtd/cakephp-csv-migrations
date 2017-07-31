<?php
use Cake\Core\Configure;

echo $this->Html->css('AdminLTE./plugins/datatables/dataTables.bootstrap', ['block' => 'css']);
echo $this->Html->script(
    [
        'AdminLTE./plugins/datatables/jquery.dataTables.min',
        'AdminLTE./plugins/datatables/dataTables.bootstrap.min',
        'CsvMigrations.importer'
    ],
    [
        'block' => 'scriptBotton'
    ]
);

$params = [
    'url' => $this->Url->build([
        'plugin' => $this->plugin,
        'controller' => $this->name,
        'action' => 'import',
        $import->id
    ]),
    'token' => Configure::read('CsvMigrations.api.token'),
    'state_duration' => (int)(Configure::read('Session.timeout') * 60)
];
echo $this->Html->scriptBlock(
    '$("#progress-table").importer(' . json_encode($params) . ');',
    ['block' => 'scriptBotton']
);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Import progress') ?></h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            <table id="progress-table" class="table table-hover table-condensed table-vertical-align" width="100%">
                <thead>
                    <tr>
                        <th><?= __('Row'); ?></th>
                        <th><?= __('Status'); ?></th>
                        <th><?= __('Status Message'); ?></th>
                        <th><?= __('Actions'); ?></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>
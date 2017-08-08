<?php
use Cake\Core\Configure;
use CsvMigrations\Model\Table\ImportsTable;
use CsvMigrations\Utility\Import as ImportUtility;

$statusLabels = [
    ImportsTable::STATUS_IN_PROGRESS => 'primary',
    ImportsTable::STATUS_COMPLETED => 'success',
    ImportsTable::STATUS_PENDING => 'warning',
    ImportsTable::STATUS_FAIL => 'error'
];

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

$totalRows = ImportUtility::getRowsCount($import->get('filename'));
$percent = round(($importCount / $totalRows) * 100, 1);
$originalLink = $this->Html->link('Original', [
    'plugin' => $this->plugin,
    'controller' => $this->name,
    'action' => 'importDownload',
    $import->get('id')
]);

$processedFile = ImportUtility::getProcessedFile($import);
$totalRecords = '-';
$processedLink = 'Processed';
if (file_exists($processedFile)) {
    $totalRecords = ImportUtility::getRowsCount($processedFile);
    $percent = round(($importCount / $totalRecords) * 100, 1);
    $processedLink = $this->Html->link('Processed', [
        'plugin' => $this->plugin,
        'controller' => $this->name,
        'action' => 'importDownload',
        $import->get('id'),
        'processed'
    ]);
}

$progressClass = 'progress-bar-info active';
if (100 === (int)$percent) {
    $progressClass = 'progress-bar-success';
}
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= basename($import->get('filename')) ?></h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-4">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <?= __('Import progress') ?>
                    </h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt><?= __('Filename') ?></dt>
                        <dd><?= basename($import->get('filename')) ?></dd>
                        <dt><?= __('Download') ?></dt>
                        <dd><?= $originalLink ?>&emsp;&emsp;<?= $processedLink ?></dd>
                        <dt><?= __('Status') ?></dt>
                        <dd>
                            <span class="label label-<?= $statusLabels[$import->get('status')] ?>">
                                <?= $import->get('status') ?>
                            </span>
                        </dd>
                        <dt><?= __('Total rows') ?></dt>
                        <dd><?= $totalRows ?></dd>
                        <dt><?= __('Total records') ?></dt>
                        <dd><?= $totalRecords ?></dd>
                        <dt><?= __('Imported records') ?></dt>
                        <dd><span class="label label-success"><?= $importCount ?></span></dd>
                        <dt><?= __('Pending records') ?></dt>
                        <dd><span class="label label-warning"><?= $pendingCount ?></span></dd>
                        <dt><?= __('Failed records') ?></dt>
                        <dd><span class="label label-danger"><?= $failCount ?></span></dd>
                        <dt><?= __('Created') ?></dt>
                        <dd><?= $import->created->i18nFormat('yyyy-MM-dd HH:mm:ss') ?></dd>
                        <dt><?= __('Modified') ?></dt>
                        <dd><?= $import->modified->i18nFormat('yyyy-MM-dd HH:mm:ss') ?></dd>
                    </dl>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped <?= $progressClass ?>" role="progressbar" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percent ?>%;">
                            <?= $percent ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
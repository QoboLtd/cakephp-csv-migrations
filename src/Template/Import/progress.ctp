<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Configure;
use CsvMigrations\Model\Table\ImportsTable;
use CsvMigrations\Utility\Import as ImportUtility;

$statusLabels = [
    ImportsTable::STATUS_IN_PROGRESS => 'primary',
    ImportsTable::STATUS_COMPLETED => 'success',
    ImportsTable::STATUS_PENDING => 'warning',
    ImportsTable::STATUS_FAIL => 'error'
];

echo $this->Html->css('Qobo/Utils./plugins/datatables/css/dataTables.bootstrap.min', ['block' => 'css']);

echo $this->Html->script(
    [
        'Qobo/Utils./plugins/datatables/datatables.min',
        'Qobo/Utils./plugins/datatables/js/dataTables.bootstrap.min',
        'CsvMigrations.importer'
    ],
    ['block' => 'scriptBottom']
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
    ['block' => 'scriptBottom']
);

$totalRows = ImportUtility::getRowsCount($import->get('filename'));
// Fix the possible division by zero on empty files
// or files with just the header
if ($totalRows <= 0) {
    $totalRows = 1;
}
$percent = round(($importCount / $totalRows) * 100, 1);
$originalLink = $this->Html->link('Original', [
    'plugin' => $this->plugin,
    'controller' => $this->name,
    'action' => 'importDownload',
    $import->get('id')
]);

$processedFile = ImportUtility::getProcessedFile($import);
$totalRecords = 0;
$processedLink = 'Processed';
if (file_exists($processedFile)) {
    $totalRecords = ImportUtility::getRowsCount($processedFile);
    // Avoid division by zero errors
    if ($totalRecords <= 0) {
        $totalRecords = 1;
    }
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
        <div class="col-xs-12">
            <h4><?= basename($import->get('filename')) ?></h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-lg-6">
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
                        <dd><?= number_format($totalRows) ?></dd>
                        <dt><?= __('Total records') ?></dt>
                        <dd><?= number_format($totalRecords) ?></dd>
                        <dt><?= __('Imported records') ?></dt>
                        <dd><span class="label label-success"><?= number_format($importCount) ?></span></dd>
                        <dt><?= __('Pending records') ?></dt>
                        <dd><span class="label label-warning"><?= number_format($pendingCount) ?></span></dd>
                        <dt><?= __('Failed records') ?></dt>
                        <dd><span class="label label-danger"><?= number_format($failCount) ?></span></dd>
                        <dt><?= __('Attempts') ?></dt>
                        <dd><?= $import->attempts ?> / <?= Configure::read('Importer.max_attempts') ?></dd>
                        <dt><?= __('Created') ?></dt>
                        <dd><?= $import->created->i18nFormat('yyyy-MM-dd HH:mm:ss') ?></dd>
                        <dt><?= __('Last Attempt') ?></dt>
                        <dd><?= $import->get('attempted_date') ?
                            $import->attempted_date->i18nFormat('yyyy-MM-dd HH:mm:ss') :
                            '-'
                        ?></dd>
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

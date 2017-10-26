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
use Cake\Event\Event;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);

// Setup Index View js logic
echo $this->Html->css(
    [
        'CsvMigrations.style',
        'Qobo/Utils./plugins/datatables/css/dataTables.bootstrap.min',
        'Qobo/Utils./plugins/datatables/extensions/Select/css/select.bootstrap.min',
        'Qobo/Utils./css/dataTables.batch'
    ],
    ['block' => 'css']
);
echo $this->Html->script(
    [
        'Qobo/Utils./plugins/datatables/datatables.min',
        'Qobo/Utils./plugins/datatables/js/dataTables.bootstrap.min',
        'Qobo/Utils./plugins/datatables/extensions/Select/js/dataTables.select.min',
        'Qobo/Utils.dataTables.init',
        'CsvMigrations.view-index'
    ],
    ['block' => 'scriptBottom']
);

$dataTableOptions = [
    'table_id' => '.table-datatable',
    'state' => ['duration' => (int)(Configure::read('Session.timeout') * 60)],
    'ajax' => [
        'token' => Configure::read('CsvMigrations.api.token'),
        'url' => $this->Url->build([
            'prefix' => 'api',
            'plugin' => $this->request->plugin,
            'controller' => $this->request->controller,
            'action' => $this->request->action
        ]) . '.json',
        'extras' => ['format' => 'datatables', 'menus' => 1]
    ],
];
if (Configure::read('CsvMigrations.batch.active')) {
    $dataTableOptions['batch'] = ['id' => Configure::read('CsvMigrations.batch.button_id')];
}

echo $this->Html->scriptBlock(
    '// initialize index view functionality
    view_index.init({
        token: "' . Configure::read('CsvMigrations.api.token') . '",
        // initialize dataTable
        datatable: datatables_init.init(' . json_encode($dataTableOptions) . ')
    });',
    ['block' => 'scriptBottom']
);

$defaultOptions = [
    'title' => null,
    'fields' => [],
];
if (empty($options)) {
    $options = [];
}
$options = array_merge($defaultOptions, $options);

// Get title from controller
if (empty($options['title'])) {
    $options['title'] = Inflector::humanize(Inflector::underscore($moduleAlias));
}
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= $options['title'] ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <?= $this->element('CsvMigrations.Menu/index_top', ['user' => $user]) ?>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-hover table-condensed table-vertical-align table-datatable" width="100%">
                    <thead>
                        <tr>
                        <?php if (Configure::read('CsvMigrations.batch.active')) : ?>
                            <th class="dt-select-column"></th>
                        <?php endif; ?>
                        <?php foreach ($options['fields'] as $field) : ?>
                        <?php
                        $tableName = $field[0]['model'];
                        if (!is_null($field[0]['plugin'])) {
                            $tableName = $field[0]['plugin'] . '.' . $tableName;
                        }
                        ?>
                            <th><?= $fhf->renderName($tableName, $field[0]['name']) ?></th>
                        <?php endforeach; ?>
                        <th><?= __('Actions'); ?></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>

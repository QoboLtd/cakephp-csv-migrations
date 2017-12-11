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
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

$factory = new FieldHandlerFactory($this);

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

$dtOptions = [
    'table_id' => '.table-datatable',
    'state' => ['duration' => (int)(Configure::read('Session.timeout') * 60)],
    'ajax' => [
        'token' => Configure::read('CsvMigrations.api.token'),
        'url' => $this->Url->build([
            'prefix' => 'api',
            'plugin' => $this->plugin,
            'controller' => $this->name,
            'action' => $this->request->param('action')
        ]) . '.json',
        'columns' => call_user_func(function () use ($options) {
            $result = [];

            // add primary key to DataTable columns if batch is active
            if (Configure::read('CsvMigrations.batch.active')) {
                $tableName = $this->plugin ? $this->plugin . '.' . $this->name : $this->name;
                $table = TableRegistry::get($tableName);
                $result[] = $table->getPrimaryKey();
            }

            foreach ($options['fields'] as $field) {
                $result[] = $field[0]['name'];
            }
            $result[] = '_Menus';

            return $result;
        }),
        'virtualColumns' => call_user_func(function () {
            $mc = new ModuleConfig(ConfigType::MODULE(), $this->name);
            $config = $mc->parse();

            return (array)$config->virtualFields;
        }),
        'combinedColumns' => call_user_func(function () use ($options, $factory) {
            $mc = new ModuleConfig(ConfigType::MIGRATION(), $this->name);
            $config = $mc->parse();

            $result = [];
            foreach ($options['fields'] as $field) {
                $fieldName = $field[0]['name'];
                if (!property_exists($config, $fieldName)) {
                    continue;
                }

                $tableName = $this->plugin ? $this->plugin . '.' . $this->name : $this->name;

                $csvField = new CsvField((array)$config->{$fieldName});
                // convert CSV field to DB field(s)
                $dbFields = $factory->fieldToDb($csvField, $tableName, $fieldName);
                // non-combined field
                if (isset($dbFields[$fieldName])) {
                    continue;
                }

                foreach ($factory->fieldToDb($csvField, $tableName, $fieldName) as $dbField) {
                    $result[$fieldName][] = $dbField->getName();
                }
            }

            return $result;
        }),
        'extras' => ['format' => 'pretty', 'menus' => 1]
    ],
];
if (Configure::read('CsvMigrations.batch.active')) {
    $dtOptions['batch'] = ['id' => Configure::read('CsvMigrations.batch.button_id')];
    $dtOptions['order'] = [1, 'asc'];
}

echo $this->Html->scriptBlock(
    '// initialize index view functionality
    view_index.init({
        token: "' . Configure::read('CsvMigrations.api.token') . '",
        // initialize dataTable
        datatable: new DataTablesInit(' . json_encode($dtOptions) . ')
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
                <?= $this->element('CsvMigrations.Menu/index_top') ?>
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
                            <th><?= $factory->renderName($tableName, $field[0]['name']) ?></th>
                        <?php endforeach; ?>
                        <th><?= __('Actions'); ?></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>

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
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

list(, $module) = pluginSplit($association->className());
$mc = new ModuleConfig(ConfigType::VIEW(), $module, 'index');
$config = $mc->parse()->items;
$fields = array_column($config, 0);

$options = [
    'associationName' => $association->getName(),
    'originTable' => $table->getTable(),
    'id' => $this->request->param('pass.0'),
    'format' => 'datatables',
    'menus' => true
];

$tableId = 'table-' . Inflector::underscore($association->getAlias());

$dtOptions = [
    'table_id' => '#' . $tableId,
    'state' => ['duration' => (int)(Configure::read('Session.timeout') * 60)],
    'ajax' => [
        'token' => Configure::read('CsvMigrations.api.token'),
        'url' => $url,
        'extras' => $options
    ],
];

echo $this->Html->scriptBlock('new DataTablesInit(' . json_encode($dtOptions) . ');', ['block' => 'scriptBottom']);
?>
<div class="table-responsive">
    <table id="<?= $tableId ?>" class="table table-hover table-condensed table-vertical-align" width="100%">
        <thead>
            <tr>
            <?php foreach ($fields as $field) : ?>
                <th><?= Inflector::humanize($field); ?></th>
            <?php endforeach; ?>
                <th><?= __('Actions');?></th>
            </tr>
        </thead>
    </table>
</div>
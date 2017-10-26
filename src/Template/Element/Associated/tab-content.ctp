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

$containerId = Inflector::underscore($association->getAlias());

echo $this->Html->scriptBlock(
    '$("#table-' . $containerId . '").dataTable({
        searching: false,
        processing: true,
        serverSide: true,
        paging: true,
        ajax: {
            type: "GET",
            url: "' . $url . '",
            headers: {
                "Authorization": "Bearer " + "' . Configure::read('CsvMigrations.api.token') . '"
            },
            data: function (d) {
                return $.extend( {}, d, ' . json_encode($options) . ');
            }
        },
    });',
    ['block' => 'scriptBottom']
);
?>
<div class="table-responsive">
    <table id="table-<?= $containerId; ?>" class="table table-hover table-condensed table-vertical-align" width="100%">
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
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

use Cake\Utility\Inflector;

$url = $this->Url->build([
    'prefix' => 'api',
    'controller' => $this->request->param('controller'),
    'action' => 'related'
]);
?>
<div class="tab-content">
    <?php $active = 'active'; ?>
    <?php foreach ($associations as $association) : ?>
        <?php $containerId = Inflector::underscore($association->getAlias()); ?>
        <div role="tabpanel" class="tab-pane <?= $active ?>" id="<?= $containerId ?>">
            <?= $this->element('CsvMigrations.Associated/tab-content', [
                'association' => $association, 'table' => $table, 'url' => $url
            ]) ?>
        </div>
        <?php $active = ''; ?>
    <?php endforeach; ?>
</div> <!-- .tab-content -->
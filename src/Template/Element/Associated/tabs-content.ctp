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
?>
<div class="tab-content">
    <?php $active = 'active'; ?>
    <?php foreach ($associations as $association) : ?>
        <?php
        $url = [
            'prefix' => 'api',
            'controller' => $this->request->getParam('controller'),
            'action' => 'related',
            $entity->get($table->getPrimaryKey()),
            $association->getName()
        ];
        ?>
        <?php $containerId = Inflector::underscore($association->getAlias()); ?>
        <div role="tabpanel" class="tab-pane <?= $active ?>" id="<?= $containerId ?>">
            <?php
            if (in_array($association->type(), ['manyToMany'])) {
                echo $this->element('CsvMigrations.Embedded/lookup', ['association' => $association]);
            } ?>
            <?= $this->element('CsvMigrations.Associated/tab-content', [
                'association' => $association, 'table' => $table, 'url' => $this->Url->build($url), 'factory' => $factory, 'tableContainerId' => $containerId
            ]) ?>
        </div>
        <?php $active = ''; ?>
    <?php endforeach; ?>
</div> <!-- .tab-content -->
<?php
echo $this->Html->scriptBlock("
var tabClicked = false;
var activeTab = localStorage.getItem('activeTab_relatedTabs');

if (activeTab) {
    $('#relatedTabs li').each(function(key, element) {
        var link = $(this).find('a');
        if (activeTab == key) {
            tabClicked = true;
            $(link).click();
        }
    });
}
if (!tabClicked) {
    var activeTab = $('#relatedTabs li.active');
    if (activeTab) {
        $(activeTab).find('a').click();
    } else {
        $('#relatedTabs li').first().find('a').click();
    }
}
", ['block' => 'scriptBottom']);
?>

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
<ul id="relatedTabs" class="nav nav-tabs" role="tablist">
    <?php $active = 'active'; ?>
    <?php foreach ($associations as $association) : ?>
        <?php
        $containerId = Inflector::underscore($association->getAlias());
        list(, $label) = pluginSplit($association->className());
        $label = Inflector::humanize(Inflector::delimit($label));
        $label .= ' (' . $association->getForeignKey() . ')';
        ?>
        <li role="presentation" class="<?= $active ?>">
            <?= $this->Html->link($label, '#' . $containerId, [
                'role' => 'tab', 'data-toggle' => 'tab', 'escape' => false, 'class' => $containerId
            ]);?>
        </li>
        <?php $active = ''; ?>
    <?php endforeach; ?>
</ul>

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

use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$factory = new FieldHandlerFactory($this);

$tableName = $this->name;
if (!empty($this->plugin)) {
    $tableName = $this->plugin . '.' . $tableName;
}
?>
<div class="btn-group btn-group-sm" role="group">
    <?= $this->Html->link(
        '<i class="fa fa-pencil"></i> ' . __('Edit'),
        ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'edit', $options['entity']->id],
        ['escape' => false, 'title' => __('Edit'), 'class' => 'btn btn-default']
    ) ?>
    <?= $this->Form->postLink(
        '<i class="fa fa-trash"></i> ' . __('Delete'),
        ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'delete', $options['entity']->id],
        ['escape' => false, 'title' => __('Delete'), 'class' => 'btn btn-default',
        'confirm' => __('Are you sure you want to delete {0}?', $factory->renderValue(
            $tableName,
            $displayField,
            $options['entity']->{$displayField},
            ['renderAs' => 'plain']
        ))]
    ) ?>
</div>
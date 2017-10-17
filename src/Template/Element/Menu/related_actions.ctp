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

$factory = new FieldHandlerFactory();

$primaryKey = $table->getPrimaryKey();
$displayField = $table->getDisplayField();
list($plugin, $controller) = pluginSplit($association->className());
?>
<div class="btn-group btn-group-xs" role="group">
<?php
$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $entity->get($primaryKey)];
echo $this->Html->link('<i class="fa fa-eye"></i>', $url, [
    'title' => __('View'), 'class' => 'btn btn-default btn-sm', 'escape' => false
]);

$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'edit', $entity->get($primaryKey)];
echo $this->Html->link('<i class="fa fa-pencil"></i>', $url, [
    'title' => __('Edit'), 'class' => 'btn btn-default btn-sm', 'escape' => false
]);

$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'delete', $entity->get($primaryKey)];
echo $this->Form->postLink('<i class="fa fa-trash"></i>', $url, [
    'confirm' => __(
        'Are you sure you want to delete {0}?',
        $factory->renderValue($table, $displayField, $entity->get($displayField), ['renderAs' => 'plain'])
    ),
    'title' => __('Delete'),
    'class' => 'btn btn-default btn-sm',
    'escape' => false
]);

if (in_array($association->type(), ['manyToMany'])) {
    $url = [
        'prefix' => false,
        'plugin' => $this->request->plugin,
        'controller' => $this->request->controller,
        'action' => 'unlink',
        $options['id'],
        $association->getName(),
        $entity->get($primaryKey)
    ];
    echo $this->Form->postLink('<i class="fa fa-chain-broken"></i>', $url, [
        'title' => __('Unlink'), 'class' => 'btn btn-default btn-sm', 'escape' => false
    ]);
}
?>
</div>
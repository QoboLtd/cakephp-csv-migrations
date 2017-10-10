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
?>
<div class="btn-group btn-group-xs" role="group">
<?php
$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $entity->id];
echo $this->Html->link('<i class="fa fa-eye"></i>', $url, [
    'title' => __('View'), 'class' => 'btn btn-default', 'escape' => false
]);

$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'edit', $entity->id];
echo $this->Html->link('<i class="fa fa-pencil"></i>', $url, [
    'title' => __('Edit'), 'class' => 'btn btn-default', 'escape' => false
]);

$url = [
    'prefix' => 'api',
    'plugin' => $plugin,
    'controller' => $controller,
    'action' => 'delete',
    '_ext' => 'json',
    $entity->id
];
echo $this->Html->link('<i class="fa fa-trash"></i>', $url, [
    'title' => __('Delete'),
    'class' => 'btn btn-default',
    'escape' => false,
    'data-type' => 'ajax-delete-record',
    'data-confirm-msg' => __(
        'Are you sure you want to delete {0}?',
        $entity->has($displayField) && !empty($entity->{$displayField}) ?
            strip_tags($entity->{$displayField}) :
            'this record'
    )
]);
?>
</div>
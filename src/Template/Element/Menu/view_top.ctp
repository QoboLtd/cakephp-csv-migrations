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

use Cake\Event\Event;
use CsvMigrations\Event\EventName;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);

$tableName = $this->request->controller;
if (!empty($this->request->plugin)) {
    $tableName = $this->request->plugin . '.' . $tableName;
}

$menu = [];

$url = [
    'plugin' => $this->request->plugin,
    'controller' => $this->request->controller,
    'action' => 'edit',
    $options['entity']->id
];
$menu[] = [
    'html' => $this->Html->link('<i class="fa fa-pencil"></i> ' . __('Edit'), $url, [
        'title' => __('Edit'), 'escape' => false, 'class' => 'btn btn-default'
    ]),
    'url' => $url,
    'label' => __('Edit'),
    'icon' => 'pencil',
    'type' => 'link_button',
    'order' => 90,
];

$url = [
    'plugin' => $this->request->plugin,
    'controller' => $this->request->controller,
    'action' => 'delete',
    $options['entity']->id
];

$menu[] = [
    'html' => $this->Form->postLink('<i class="fa fa-trash"></i> ' . __('Delete'), $url, [
        'confirm' => __('Are you sure you want to delete {0}?', $fhf->renderValue(
            $tableName,
            $displayField,
            $options['entity']->{$displayField},
            ['renderAs' => 'plain']
        )),
        'title' => __('Delete'),
        'escape' => false,
        'class' => 'btn btn-default'
    ]),
    'url' => $url,
    'label' => __('Delete'),
    'icon' => 'trash',
    'type' => 'postlink_button',
    'order' => 100,
    'confirmMsg' => __('Are you sure you want to delete {0}?', $fhf->renderValue(
        $tableName,
        $displayField,
        $options['entity']->{$displayField},
        ['renderAs' => 'plain']
    ))
];

$event = new Event((string)EventName::MENU_TOP_VIEW(), $this, [
    'menu' => $menu,
    'user' => $user
]);
$this->eventManager()->dispatch($event);

$result = $event->result;

echo $result;

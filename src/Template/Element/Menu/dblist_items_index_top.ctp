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

$menu = [];

$url = ['plugin' => 'CsvMigrations', 'controller' => 'DblistItems', 'action' => 'add', $entity->id];
$menu[] = [
    'html' => $this->Html->link(
        '<i class="fa fa-plus"></i> ' . __d('CsvMigrations', 'Add'),
        $url,
        ['title' => __d('CsvMigrations', 'Add'), 'escape' => false, 'class' => 'btn btn-default']
    ),
    'url' => $url,
    'label' => __('Add'),
    'icon' => 'plus',
    'type' => 'link_button',
    'order' => 10
];

// broadcast menu event
$event = new Event((string)EventName::MENU_TOP_DB_LIST_ITEMS_INDEX(), $this, [
    'menu' => $menu,
    'user' => $user
]);
$this->eventManager()->dispatch($event);

echo $event->result;

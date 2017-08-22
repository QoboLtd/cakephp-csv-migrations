<?php
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
    'label' => __d('CsvMigrations', 'Add'),
    'label' => 'plus',
    'order' => 10,
];

// broadcast menu event
$event = new Event((string)EventName::MENU_TOP_DB_LIST_ITEMS_INDEX(), $this, [
    'menu' => $menu,
    'user' => $user
]);
$this->eventManager()->dispatch($event);

echo $event->result;

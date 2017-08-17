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
    'url' => $url
];

// broadcast menu event
$event = new Event(EventName::MENU_TOP_DB_LIST_ITEMS_INDEX()->getValue(), $this, [
    'menu' => $menu,
    'user' => $user
]);
$this->eventManager()->dispatch($event);

echo $event->result;

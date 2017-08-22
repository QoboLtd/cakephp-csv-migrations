<?php
use Cake\Event\Event;
use CsvMigrations\Event\EventName;

$url = ['plugin' => 'CsvMigrations', 'controller' => 'Dblists', 'action' => 'add'];
$menu = [
    [
        'html' => $this->Html->link(
            '<i class="fa fa-plus"></i> ' . __d('CsvMigrations', 'Add'),
            $url,
            ['title' => __d('CsvMigrations', 'Add'), 'escape' => false, 'class' => 'btn btn-default']
        ),
        'url' => $url,
        'label' => __d('CsvMigrations', 'Add'),
        'label' => 'plus',
        'order' => 10,
    ]
];

// broadcast menu event
$event = new Event((string)EventName::MENU_TOP_DB_LISTS_INDEX(), $this, [
    'menu' => $menu,
    'user' => $user
]);
$this->eventManager()->dispatch($event);

echo $event->result;

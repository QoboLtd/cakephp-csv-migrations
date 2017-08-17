<?php
use Cake\Event\Event;
use CsvMigrations\Event\EventName;

$menu = [
    [
        'html' => $this->Html->link(
            '<i class="fa fa-upload"></i> ' . __('Import'),
            ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'import'],
            ['escape' => false, 'title' => __('Import Data'), 'class' => 'btn btn-default']
        ),
        'url' => ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'import']
    ],
    [
        'html' => $this->Html->link(
            '<i class="fa fa-plus"></i> ' . __('Add'),
            ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'add'],
            ['escape' => false, 'title' => __('Add'), 'class' => 'btn btn-default']
        ),
        'url' => ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'add']
    ]
];

// broadcast menu event
$event = new Event((string)EventName::MENU_TOP_INDEX(), $this, [
    'menu' => $menu,
    'user' => $user
]);
$this->eventManager()->dispatch($event);

echo $event->result;

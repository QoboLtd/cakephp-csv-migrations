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
        'url' => ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'import'],
        'icon' => 'upload',
        'label' => __('Import'),
        'type' => 'link_button',
        'order' => 10,
    ],
    [
        'html' => $this->Html->link(
            '<i class="fa fa-plus"></i> ' . __('Add'),
            ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'add'],
            ['escape' => false, 'title' => __('Add'), 'class' => 'btn btn-default']
        ),
        'url' => ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'add'],
        'icon' => 'plus',
        'label' => __('Add'),
        'type' => 'link_button',
        'order' => 20,
    ]
];

// broadcast menu event
$event = new Event((string)EventName::MENU_TOP_INDEX(), $this, [
    'menu' => $menu,
    'user' => $user
]);
$this->eventManager()->dispatch($event);

echo $event->result;

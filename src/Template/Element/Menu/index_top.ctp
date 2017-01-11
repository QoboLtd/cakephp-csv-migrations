<?php
use Cake\Event\Event;

$menu = [
    [
        'html' => $this->Html->link(
            '<i class="fa fa-plus"></i>',
            ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'add'],
            ['escape' => false]
        ),
        'url' => ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'add']
    ]
];

// broadcast menu event
$event = new Event('CsvMigrations.Index.topMenu.beforeRender', $this, [
    'menu' => $menu,
    'user' => $user
]);
$this->eventManager()->dispatch($event);

echo $event->result;
<?php
use Cake\Event\Event;

$url = ['plugin' => 'CsvMigrations', 'controller' => 'Dblists', 'action' => 'add'];
$menu = [
    [
        'html' => $this->Html->link(
            '<i class="fa fa-plus"></i> ' . __d('CsvMigrations', 'Add'),
            $url,
            ['title' => __d('CsvMigrations', 'Add'), 'escape' => false, 'class' => 'btn btn-default']
        ),
        'url' => $url
    ]
];

// broadcast menu event
$event = new Event('CsvMigrations.Dblists.Index.topMenu.beforeRender', $this, [
    'menu' => $menu,
    'user' => $user
]);
$this->eventManager()->dispatch($event);

echo $event->result;
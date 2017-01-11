<?php
use Cake\Event\Event;

$menu = [];

$url = [
    'plugin' => $this->request->plugin,
    'controller' => $this->request->controller,
    'action' => 'edit',
    $options['entity']->id
];
$menu[] = [
    'html' => $this->Html->link('<i class="fa fa-pencil"></i>', $url, [
        'title' => __('View'), 'escape' => false
    ]),
    'url' => $url
];

$url = [
    'plugin' => $this->request->plugin,
    'controller' => $this->request->controller,
    'action' => 'delete',
    $options['entity']->id
];
$menu[] = [
    'html' => $this->Form->postLink('<i class="fa fa-trash"></i>', $url, [
        'confirm' => __('Are you sure you want to delete {0}?', $options['entity']->{$displayField}),
        'title' => __('Delete'),
        'escape' => false
    ]),
    'url' => $url
];

// broadcast menu event
$event = new Event('CsvMigrations.View.topMenu.beforeRender', $this, [
    'menu' => $menu,
    'user' => $user
]);
$this->eventManager()->dispatch($event);

echo $event->result;
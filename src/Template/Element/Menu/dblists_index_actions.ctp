<?php
use Cake\Event\Event;
use CsvMigrations\Event\EventName;

$menu = [];

$url = [
    'plugin' => 'CsvMigrations',
    'controller' => 'DblistItems',
    'action' => 'index',
    $entity->id
];
$menu[] = [
    'html' => $this->Html->link('<i class="fa fa-list-alt"></i>', $url, [
        'title' => __('View'), 'class' => 'btn btn-default', 'escape' => false
    ]),
    'url' => $url,
    'label' => __('View'),
    'label' => 'list-alt',
    'order' => 10,
];

$url = [
    'plugin' => 'CsvMigrations',
    'controller' => 'Dblists',
    'action' => 'edit',
    $entity->id
];
$menu[] = [
    'html' => $this->Html->link('<i class="fa fa-pencil"></i>', $url, [
        'title' => __('Edit'), 'class' => 'btn btn-default', 'escape' => false
    ]),
    'url' => $url,
    'label' => __('Edit'),
    'label' => 'pencil',
    'order' => 20,
];

$url = [
    'plugin' => 'CsvMigrations',
    'controller' => 'Dblists',
    'action' => 'delete',
    $entity->id
];
$menu[] = [
    'html' => $this->Form->postLink('<i class="fa fa-trash"></i>', $url, [
        'title' => __('Delete'),
        'class' => 'btn btn-default btn-sm',
        'escape' => false,
        'confirm' => __d('CsvMigrations', 'Are you sure you want to delete {0}?', $entity->name)
    ]),
    'url' => $url,
    'label' => __('Delete'),
    'icon' => 'trash',
    'type' => 'postlink_button',
    'order' => 30,
    'confirmMsg' => __d('CsvMigrations', 'Are you sure you want to delete {0}?', $entity->name)
];

// broadcast menu event
$event = new Event((string)EventName::MENU_ACTIONS_DB_LISTS_INDEX(), $this, [
    'menu' => $menu,
    'user' => $user
]);
$this->EventManager()->dispatch($event);

echo '<div class="btn-group btn-group-xs" role="group">' . $event->result . '</div>';

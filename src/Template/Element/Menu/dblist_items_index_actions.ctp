<?php
use Cake\Event\Event;
use CsvMigrations\Event\EventName;

$menu = [];

$url = [
    'plugin' => 'CsvMigrations',
    'controller' => 'DblistItems',
    'action' => 'move_node',
    $entity->id,
    'up'
];
$menu[] = [
    'html' => $this->Form->postLink('<i class="fa fa-arrow-up"></i>', $url, [
        'title' => __('Move up'),
        'class' => 'btn btn-default',
        'escape' => false
    ]),
    'url' => $url,
    'label' => __('Move up'),
    'icon' => 'arrow-up',
    'type' => 'postlink_button',
    'order' => 10,
];

$url = [
    'plugin' => 'CsvMigrations',
    'controller' => 'DblistItems',
    'action' => 'move_node',
    $entity->id,
    'down'
];
$menu[] = [
    'html' => $this->Form->postLink('<i class="fa fa-arrow-down"></i>', $url, [
        'title' => __('Move down'),
        'class' => 'btn btn-default',
        'escape' => false
    ]),
    'url' => $url,
    'label' => __('Move down'),
    'icon' => 'arrow-down',
    'type' => 'postlink_button',
    'order' => 20,
];

$url = [
    'plugin' => 'CsvMigrations',
    'controller' => 'DblistItems',
    'action' => 'edit',
    $entity->id
];
$menu[] = [
    'html' => $this->Html->link('<i class="fa fa-pencil"></i>', $url, [
        'title' => __('Edit'), 'class' => 'btn btn-default', 'escape' => false
    ]),
    'url' => $url,
    'label' => __('Edit'),
    'icon' => 'pencil',
    'type' => 'link_button',
    'order' => 30,
];

$url = [
    'plugin' => 'CsvMigrations',
    'controller' => 'DblistItems',
    'action' => 'delete',
    $entity->id
];
$menu[] = [
    'html' => $this->Form->postLink('<i class="fa fa-trash"></i>', $url, [
        'title' => __('Delete'),
        'class' => 'btn btn-default',
        'escape' => false,
        'confirm' => __d('CsvMigrations', 'Are you sure you want to delete {0}?', $entity->name)
    ]),
    'url' => $url,
    'label' => __('Delete'),
    'icon' => 'trash',
    'type' => 'postlink_button',
    'order' => 40,
    'confirmMsg' => __d('CsvMigrations', 'Are you sure you want to delete {0}?', $entity->name)
];

// broadcast menu event
$event = new Event((string)EventName::MENU_ACTIONS_DB_LIST_ITEMS_INDEX(), $this, [
    'menu' => $menu,
    'user' => $user,
    'type' => 'actions',
]);
$this->EventManager()->dispatch($event);

echo '<div class="btn-group btn-group-xs" role="group">' . $event->result . '</div>';

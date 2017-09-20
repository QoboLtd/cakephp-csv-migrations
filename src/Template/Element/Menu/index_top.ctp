<?php
use Cake\Event\Event;
use CsvMigrations\Event\EventName;

$batch['button'] = $this->Form->button('<i class="fa fa-bars"></i> Batch', [
    'id' => 'batch-button',
    'type' => 'button',
    'class' => 'btn btn-default dropdown-toggle',
    'data-toggle' => 'dropdown',
    'aria-haspopup' => 'true',
    'aria-expanded' => 'false',
    'disabled' => true
]);
$batch['edit'] = $this->Html->link(
    '<i class="fa fa-pencil"></i> ' . __('Edit'),
    ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch'],
    [
        'id' => 'batch-edit-button',
        'escape' => false
    ]
);
$batch['delete'] = $this->Html->link(
    '<i class="fa fa-trash"></i> ' . __('Delete'),
    ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch'],
    [
        'id' => 'batch-delete-button',
        'escape' => false
    ]
);

$menu = [
    [
        'url' => ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch'],
        'type' => 'link',
        'raw_html' => $batch['button'] . '<ul class="dropdown-menu">
            <li>' . $batch['edit'] . '</li>
            <li>' . $batch['delete'] . '</li>
        </ul>'
    ],
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

<?php
use Cake\Event\Event;

foreach ($entities as $entity) {
    $menu = [];

    $url = [
        'prefix' => false,
        'plugin' => $plugin,
        'controller' => $controller,
        'action' => 'view',
        $entity->id
    ];
    $menu[] = [
        'html' => $this->Html->link('<i class="fa fa-eye"></i>', $url, [
            'title' => __('View'), 'class' => 'btn btn-default btn-sm', 'escape' => false
        ]),
        'url' => $url
    ];

    $url = [
        'prefix' => false,
        'plugin' => $plugin,
        'controller' => $controller,
        'action' => 'edit',
        $entity->id
    ];
    $menu[] = [
        'html' => $this->Html->link('<i class="fa fa-pencil"></i>', $url, [
            'title' => __('Edit'), 'class' => 'btn btn-default btn-sm', 'escape' => false
        ]),
        'url' => $url
    ];

    $url = [
        'prefix' => 'api',
        'plugin' => $plugin,
        'controller' => $controller,
        'action' => 'delete',
        '_ext' => 'json',
        $entity->id
    ];
    $menu[] = [
        'html' => $this->Html->link('<i class="fa fa-trash"></i>', $url, [
            'title' => __('Delete'),
            'class' => 'btn btn-default btn-sm',
            'escape' => false,
            'data-type' => 'ajax-delete-record',
            'data-confirm-msg' => __(
                'Are you sure you want to delete {0}?',
                $entity->has($displayField) && !empty($entity->{$displayField}) ? $entity->{$displayField} : 'this record'
            )
        ]),
        'url' => $url
    ];

    // broadcast menu event
    $event = new Event('CsvMigrations.Index.actionsMenu.beforeRender', $this, [
        'menu' => $menu,
        'user' => $user
    ]);
    $this->EventManager()->dispatch($event);

    $entity->{$propertyName} = $event->result;
}
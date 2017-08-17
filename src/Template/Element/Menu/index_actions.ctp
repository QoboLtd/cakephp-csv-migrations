<?php
use Cake\Event\Event;
use CsvMigrations\Event\EventName;

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
            'title' => __('View'), 'class' => 'btn btn-default', 'escape' => false
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
            'title' => __('Edit'), 'class' => 'btn btn-default', 'escape' => false
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
            'class' => 'btn btn-default',
            'escape' => false,
            'data-type' => 'ajax-delete-record',
            'data-confirm-msg' => __(
                'Are you sure you want to delete {0}?',
                $entity->has($displayField) && !empty($entity->{$displayField}) ?
                    strip_tags($entity->{$displayField}) :
                    'this record'
            )
        ]),
        'url' => $url
    ];

    // broadcast menu event
    $event = new Event((string)EventName::MENU_ACTIONS_ASSOCIATED(), $this, [
        'menu' => $menu,
        'user' => $user
    ]);
    $this->EventManager()->dispatch($event);

    $result = '<div class="btn-group btn-group-xs" role="group">' . $event->result . '</div>';
    $entity->{$propertyName} = $result;
}

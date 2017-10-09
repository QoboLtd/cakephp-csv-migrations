<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

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
        'url' => $url,
        'icon' => 'eye',
        'label' => __('View'),
        'type' => 'link_button',
        'order' => 10,
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
        'url' => $url,
        'icon' => 'pencil',
        'label' => __('Edit'),
        'type' => 'link_button',
        'order' => 20,
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
        'url' => $url,
        'icon' => 'trash',
        'label' => __('Delete'),
        'dataType' => 'ajax-delete-record',
        'type' => 'link_button',
        'confirmMsg' => __(
            'Are you sure you want to delete {0}?',
            $entity->has($displayField) && !empty($entity->{$displayField}) ?
                    strip_tags($entity->{$displayField}) :
                    'this record'
        ),
        'order' => 30,

    ];

    // broadcast menu event
    $event = new Event((string)EventName::MENU_ACTIONS_ASSOCIATED(), $this, [
        'menu' => $menu,
        'user' => $user,
        'type' => 'actions',
    ]);
    $this->EventManager()->dispatch($event);

    $result = '<div class="btn-group btn-group-xs" role="group">' . $event->result . '</div>';
    $entity->{$propertyName} = $result;
}

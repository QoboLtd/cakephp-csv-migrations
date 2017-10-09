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
use Cake\Utility\Hash;
use CsvMigrations\Event\EventName;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory();

if (empty($user) && !empty($_SESSION)) {
    $user = Hash::get($_SESSION, 'Auth.User');
}

$menu = [];

list($plugin, $controller) = pluginSplit($options['targetClass']);

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
    'url' => $url,
    'label' => __('View'),
    'icon' => 'eye',
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
        'title' => __('Edit'), 'class' => 'btn btn-default btn-sm', 'escape' => false
    ]),
    'url' => $url,
    'label' => __('Edit'),
    'icon' => 'pencil',
    'type' => 'link_button',
    'order' => 20,
];

$url = [
    'prefix' => false,
    'plugin' => $plugin,
    'controller' => $controller,
    'action' => 'delete',
    $entity->id,
];

$menu[] = [
    'html' => $this->Form->postLink('<i class="fa fa-trash"></i>', $url, [
        'confirm' => __(
            'Are you sure you want to delete {0}?',
            $fhf->renderValue(
                $options['class_name'],
                $options['display_field'],
                $entity->{$options['display_field']},
                ['renderAs' => 'plain']
            )
        ),
        'title' => __('Delete'),
        'class' => 'btn btn-default btn-sm',
        'escape' => false
    ]),
    'url' => $url,
    'label' => __('Delete'),
    'icon' => 'trash',
    'type' => 'postlink_button',
    'order' => 40,
    'confirmMsg' => __(
        'Are you sure you want to delete {0}?',
        $fhf->renderValue(
            $options['class_name'],
            $options['display_field'],
            $entity->{$options['display_field']},
            ['renderAs' => 'plain']
        )
    ),
];

if (isset($options['associationType']) && in_array($options['associationType'], ['manyToMany'])) {
    $url = [
        'prefix' => false,
        'plugin' => $this->request->plugin,
        'controller' => $this->request->controller,
        'action' => 'unlink',
        $options['id'],
        $options['associationName'],
        $entity->id
    ];
    $menu[] = [
        'html' => $this->Form->postLink('<i class="fa fa-chain-broken"></i>', $url, [
            'title' => __('Unlink'), 'class' => 'btn btn-default btn-sm', 'escape' => false
        ]),
        'url' => $url,
        'label' => __('Unlink'),
        'icon' => 'unlink',
        'type' => 'postlink_button',
        'order' => 30,
    ];
}

// broadcast menu event
$event = new Event((string)EventName::MENU_ACTIONS_ASSOCIATED(), $this, [
    'menu' => $menu,
    'user' => $user,
    'type' => 'actions',
]);
$this->eventManager()->dispatch($event);

echo '<div class="btn-group btn-group-xs" role="group">' . $event->result . '</div>';

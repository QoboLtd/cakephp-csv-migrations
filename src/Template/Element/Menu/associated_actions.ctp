<?php
use Cake\Event\Event;
use Cake\Utility\Hash;
use CsvMigrations\Event\EventName;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);

if (empty($user) && !empty($_SESSION)) {
    $user = Hash::get($_SESSION, 'Auth.User');
}

$menu = [];

list($plugin, $controller) = pluginSplit($options['associated']['className']);

$url = [
    'prefix' => false,
    'plugin' => $plugin,
    'controller' => $controller,
    'action' => 'view',
    $options['associated']['entity']->id
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
    $options['associated']['entity']->id
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
    $options['associated']['entity']->id,
];

$menu[] = [
    'html' => $this->Form->postLink('<i class="fa fa-trash"></i>', $url, [
        'confirm' => __(
            'Are you sure you want to delete {0}?',
            $fhf->renderValue(
                $options['associated']['className'],
                $options['associated']['displayField'],
                $options['associated']['entity']->{$options['associated']['displayField']},
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
    'order' => 30,
    'confirmMsg' => __(
        'Are you sure you want to delete {0}?',
        $fhf->renderValue(
            $options['associated']['className'],
            $options['associated']['displayField'],
            $options['associated']['entity']->{$options['associated']['displayField']},
            ['renderAs' => 'plain']
        )
    ),
];

if (isset($options['associated']['type']) && in_array($options['associated']['type'], ['manyToMany'])) {
    $url = [
        'plugin' => $this->request->plugin,
        'controller' => $this->request->controller,
        'action' => 'unlink',
        $options['entity']->id,
        $options['associated']['name'],
        $options['associated']['entity']->id
    ];
    $menu[] = [
        'html' => $this->Form->postLink('<i class="fa fa-chain-broken"></i>', $url, [
            'title' => __('Unlink'), 'class' => 'btn btn-default btn-sm', 'escape' => false
        ]),
        'url' => $url
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

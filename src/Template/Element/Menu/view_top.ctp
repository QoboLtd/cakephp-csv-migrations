<?php
use Cake\Event\Event;
use CsvMigrations\Event\EventName;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);

$tableName = $this->request->controller;
if (!empty($this->request->plugin)) {
    $tableName = $this->request->plugin . '.' . $tableName;
}

$menu = [];

$url = [
    'plugin' => $this->request->plugin,
    'controller' => $this->request->controller,
    'action' => 'edit',
    $options['entity']->id
];
$menu[] = [
    'html' => $this->Html->link('<i class="fa fa-pencil"></i> ' . __('Edit'), $url, [
        'title' => __('Edit'), 'escape' => false, 'class' => 'btn btn-default'
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
    'html' => $this->Form->postLink('<i class="fa fa-trash"></i> ' . __('Delete'), $url, [
        'confirm' => __('Are you sure you want to delete {0}?', $fhf->renderValue(
            $tableName,
            $displayField,
            $options['entity']->{$displayField},
            ['renderAs' => 'plain']
        )),
        'title' => __('Delete'),
        'escape' => false,
        'class' => 'btn btn-default'
    ]),
    'url' => $url
];

// broadcast menu event
$event = new Event(EventName::MENU_TOP_VIEW()->getValue(), $this, [
    'menu' => $menu,
    'user' => $user
]);
$this->eventManager()->dispatch($event);

echo $event->result;

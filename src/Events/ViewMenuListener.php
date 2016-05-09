<?php
namespace CsvMigrations\Events;

use App\View\AppView;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Network\Request;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

class ViewMenuListener implements EventListenerInterface
{
    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'View.Index.Menu.Top' => 'getIndexMenuTop',
            'View.Index.Menu.Actions' => 'getIndexMenuActions',
            'View.View.Menu.Top' => 'getViewMenuTop'
        ];
    }

    /**
     * Method that adds elements to index View top menu.
     *
     * @param  Cake\Event\Event     $event   Event object
     * @param  Cake\Network\Request $request Request object
     * @param  array                $options Entity options
     * @return undefined
     */
    public function getIndexMenuTop(Event $event, Request $request, array $options)
    {
        $appView = new AppView();

        return $appView->Html->link(
            __('Add {0}', Inflector::singularize($options['title'])),
            ['plugin' => $request->plugin, 'controller' => $request->controller, 'action' => 'add'],
            ['class' => 'btn btn-primary']
        );
    }

    /**
     * Method that adds elements to index View actions menu.
     *
     * @param  Cake\Event\Event     $event   Event object
     * @param  Cake\Network\Request $request Request object
     * @param  Cake\ORM\Entity      $options Entity options
     * @return undefined
     */
    public function getIndexMenuActions(Event $event, Request $request, Entity $options)
    {
        $appView = new AppView();

        $controllerName = $request->controller;
        if (!empty($request->plugin)) {
            $controllerName = $request->plugin . '.' . $controllerName;
        }

        $displayField = TableRegistry::get($controllerName)->displayField();

        $result = $appView->Html->link(
            '',
            ['action' => 'view', $options->id],
            ['title' => __('View'), 'class' => 'btn btn-default glyphicon glyphicon-eye-open']
        );
        $result .= ' ' . $appView->Html->link(
            '',
            ['action' => 'edit', $options->id],
            ['title' => __('Edit'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']
        );
        $result .= ' ' . $appView->Form->postLink(
            '',
            ['action' => 'delete', $options->id],
            [
                'confirm' => __('Are you sure you want to delete {0}?', $options->{$displayField}),
                'title' => __('Delete'),
                'class' => 'btn btn-default glyphicon glyphicon-trash'
            ]
        );

        return $result;
    }

    /**
     * Method that adds elements to view View top menu.
     *
     * @param  Cake\Event\Event     $event   Event object
     * @param  Cake\Network\Request $request Request object
     * @param  array                $options Entity options
     * @return undefined
     */
    public function getViewMenuTop(Event $event, Request $request, array $options)
    {
        $result = '';
        $appView = new AppView();

        $controllerName = $request->controller;
        if (!empty($request->plugin)) {
            $controllerName = $request->plugin . '.' . $controllerName;
        }

        $result .= $appView->Html->link(
            '',
            ['plugin' => null, 'controller' => 'log_audit', 'action' => 'changelog', $controllerName, $options['entity']->id],
            ['title' => __('Changelog'), 'class' => 'btn btn-default glyphicon glyphicon-book']
        );

        $result .= ' ' . $appView->Html->link(
            '',
            ['action' => 'edit', $options['entity']->id],
            ['title' => __('Edit'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']
        );
        $result .= ' ' . $appView->Form->postLink(
            '',
            ['action' => 'delete', $options['entity']->id],
            [
                'confirm' => __('Are you sure you want to delete {0}?', $options['entity']->id),
                'title' => __('Delete'),
                'class' => 'btn btn-default glyphicon glyphicon-trash'
            ]
        );

        return $result;
    }
}
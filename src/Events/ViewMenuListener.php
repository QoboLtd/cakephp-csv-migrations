<?php
namespace CsvMigrations\Events;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Network\Request;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\View\AppView;

class ViewMenuListener implements EventListenerInterface
{
    /**
     * Menu element name
     */
    const MENU_ELEMENT = 'Menu.menu';

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
            'View.Associated.Menu.Actions' => 'getAssociatedMenuActions',
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

        $urlAdd = ['plugin' => $request->plugin, 'controller' => $request->controller, 'action' => 'add'];

        $btnAdd = $appView->Html->link(
            __('Add {0}', Inflector::singularize($options['title'])),
            $urlAdd,
            ['class' => 'btn btn-primary']
        );

        $menu = [
            [
                'label' => $btnAdd,
                'url' => $urlAdd,
                'capabilities' => 'fromUrl'
            ]
        ];

        if ($appView->elementExists(static::MENU_ELEMENT)) {
            $result = $appView->element(static::MENU_ELEMENT, ['menu' => $menu, 'renderAs' => 'provided']);
        } else {
            $result = $btnAdd;
        }

        return $result;
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

        $urlView = [
            'plugin' => $request->plugin,
            'controller' => $request->controller,
            'action' => 'view',
            $options->id
        ];
        $btnView = $appView->Html->link(
            '',
            $urlView,
            ['title' => __('View'), 'class' => 'btn btn-default glyphicon glyphicon-eye-open']
        );

        $urlEdit = [
            'plugin' => $request->plugin,
            'controller' => $request->controller,
            'action' => 'edit',
            $options->id
        ];
        $btnEdit = ' ' . $appView->Html->link(
            '',
            $urlEdit,
            ['title' => __('Edit'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']
        );

        $urlDel = [
            'plugin' => $request->plugin,
            'controller' => $request->controller,
            'action' => 'delete',
            $options->id
        ];
        $btnDel = ' ' . $appView->Form->postLink(
            '',
            $urlDel,
            [
                'confirm' => __('Are you sure you want to delete {0}?', $options->{$displayField}),
                'title' => __('Delete'),
                'class' => 'btn btn-default glyphicon glyphicon-trash'
            ]
        );

        $menu = [
            [
                'label' => $btnView,
                'url' => $urlView,
                'capabilities' => 'fromUrl'
            ],
            [
                'label' => $btnEdit,
                'url' => $urlEdit,
                'capabilities' => 'fromUrl'
            ],
            [
                'label' => $btnDel,
                'url' => $urlDel,
                'capabilities' => 'fromUrl'
            ]
        ];

        if ($appView->elementExists(static::MENU_ELEMENT)) {
            $result = $appView->element(static::MENU_ELEMENT, ['menu' => $menu, 'renderAs' => 'provided']);
        } else {
            $result = $btnView . $btnEdit . $btnDel;
        }

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
        $appView = new AppView();

        $controllerName = $request->controller;
        if (!empty($request->plugin)) {
            $controllerName = $request->plugin . '.' . $controllerName;
        }

        $displayField = TableRegistry::get($controllerName)->displayField();

        $urlChangelog = [
            'plugin' => null,
            'controller' => 'log_audit',
            'action' => 'changelog',
            $controllerName,
            $options['entity']->id
        ];
        $btnChangelog = ' ' . $appView->Html->link(
            '',
            $urlChangelog,
            ['title' => __('Changelog'), 'class' => 'btn btn-default glyphicon glyphicon-book']
        );

        $urlEdit = [
            'plugin' => $request->plugin,
            'controller' => $request->controller,
            'action' => 'edit',
            $options['entity']->id
        ];
        $btnEdit = ' ' . $appView->Html->link(
            '',
            $urlEdit,
            ['title' => __('Edit'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']
        );

        $urlDel = [
            'plugin' => $request->plugin,
            'controller' => $request->controller,
            'action' => 'delete',
            $options['entity']->id
        ];
        $btnDel = ' ' . $appView->Form->postLink(
            '',
            $urlDel,
            [
                'confirm' => __('Are you sure you want to delete {0}?', $options['entity']->{$displayField}),
                'title' => __('Delete'),
                'class' => 'btn btn-default glyphicon glyphicon-trash'
            ]
        );

        $menu = [];

        $menu[] = [
            'label' => $btnChangelog,
            'url' => $urlChangelog,
            'capabilities' => 'fromUrl'
        ];
        $menu[] = [
            'label' => $btnEdit,
            'url' => $urlEdit,
            'capabilities' => 'fromUrl'
        ];
        $menu[] = [
            'label' => $btnDel,
            'url' => $urlDel,
            'capabilities' => 'fromUrl'
        ];

        $html = null;
        if ($appView->elementExists(static::MENU_ELEMENT)) {
            $html .= $appView->element(static::MENU_ELEMENT, ['menu' => $menu, 'renderAs' => 'provided']);
        } else {
            $html .= $btnChangelog . $btnEdit . $btnDel;
        }

        $event->result = $html . $event->result;

        return $event->result;
    }

    /**
     * Method that adds elements to associated Element actions menu.
     *
     * @param  Cake\Event\Event     $event   Event object
     * @param  Cake\Network\Request $request Request object
     * @param  Cake\ORM\Entity      $options Entity options
     * @return undefined
     */
    public function getAssociatedMenuActions(Event $event, Request $request, array $options)
    {
        $appView = new AppView();

        $controllerName = $request->controller;
        if (!empty($request->plugin)) {
            $controllerName = $request->plugin . '.' . $controllerName;
        }

        $urlUnlink = [
            'plugin' => $request->plugin,
            'controller' => $request->controller,
            'action' => 'unlink',
            $options['entity']->id,
            $options['assoc_name'],
            $options['assoc_entity']->id
        ];

        $btnUnlink = $appView->Form->postLink(
            '',
            $urlUnlink,
            ['title' => __('Unlink'), 'class' => 'btn btn-default fa fa-chain-broken']
        );

        $menu = [
            [
                'label' => $btnUnlink,
                'url' => $urlUnlink,
                'capabilities' => 'fromUrl'
            ]
        ];

        if ($appView->elementExists(static::MENU_ELEMENT)) {
            $result = $appView->element(static::MENU_ELEMENT, ['menu' => $menu, 'renderAs' => 'provided']);
        } else {
            $result = $btnUnlink;
        }

        return $result;
    }
}

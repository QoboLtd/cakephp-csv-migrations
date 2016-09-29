<?php
namespace CsvMigrations\Events;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Network\Request;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use CsvMigrations\View\AppView;

class ViewMenuListener implements EventListenerInterface
{
    /**
     * Event listener type
     */
    const EVENT_TYPE = 'menus';

    /**
     * Menu element name
     */
    const MENU_ELEMENT = 'Menu.menu';

    /**
     * Blank space
     */
    const BLANK_SPACE = '&nbsp;';

    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        $menus = Configure::read('CsvMigrations.menus');
        $events = Configure::read('CsvMigrations.events');

        $result = [];
        foreach ($events as $type) {
            if (empty($type[static::EVENT_TYPE])) {
                continue;
            }
            foreach ($type[static::EVENT_TYPE] as $key => $value) {
                $result[key($value)] = current($value);
            }
        }

        return $result;
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
            'prefix' => false,
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
            'prefix' => false,
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
            'prefix' => false,
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
            $result = $appView->element(
                static::MENU_ELEMENT,
                ['menu' => $menu, 'renderAs' => 'provided', 'user' => $event->subject()->Auth->user()]
            );
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
     * Creates menu with buttons.
     *
     * Button Options
     * - title*: Title
     * - icon*: Glyphicon class
     * - url*: CakePHP array format only
     * - class: CSS classes
     *
     * @param  Cake\Event\Event     $event   Event object
     * @param  Cake\Network\Request $request Request object
     * @param  array                $options Entity options
     * @return string
     */
    public function getViewMenuTopRow(Event $event, Request $request, array $options)
    {
        $view = $event->subject();
        $menu = [];
        $result = '';
        foreach ($options as $key => $btOptions) {
            $icon = Hash::get($btOptions, 'icon', false);
            $title = Hash::get($btOptions, 'title', false);
            if (!$icon && !$title) {
                continue;
            }
            $url = Hash::get($btOptions, 'url');
            //This is for the menu plugin which requires URL in array format.
            if (!is_array($url)) {
                continue;
            }
            unset($btOptions['url']);
            $name = '';
            if ($icon) {
                $name .= $view->Html->icon($icon) . static::BLANK_SPACE;
                $btOptions += ['escape' => false];
                unset($btOptions['icon']);
            }
            if ($title) {
                $name .= $title;
            }
            $btn = static::BLANK_SPACE . $view->Html->link(
                $name,
                $url,
                $btOptions
            );
            //insert to menu
            $menu[] = [
                'label' => $btn,
                'url' => $url,
                'capabilities' => 'fromUrl'
            ];
            //Insert to variable if menu is not loaded.
            $result .= $btn;
        }

        $html = null;
        if ($view->elementExists(static::MENU_ELEMENT)) {
            $html .= $view->element(static::MENU_ELEMENT, ['menu' => $menu, 'renderAs' => 'provided']);
        } else {
            $html .= $result;
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

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

        $btnAdd = $appView->Html->link(
            __('Add {0}', Inflector::singularize($options['title'])),
            ['plugin' => $request->plugin, 'controller' => $request->controller, 'action' => 'add'],
            ['class' => 'btn btn-primary']
        );


        $menu = [
            [
                'label' => $btnAdd,
                'url' => [
                    'plugin' => $request->plugin,
                    'controller' => $request->controller,
                    'action' => 'add'
                ],
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

        $btnView = $appView->Html->link(
            '',
            ['action' => 'view', $options->id],
            ['title' => __('View'), 'class' => 'btn btn-default glyphicon glyphicon-eye-open']
        );
        $btnEdit = ' ' . $appView->Html->link(
            '',
            ['action' => 'edit', $options->id],
            ['title' => __('Edit'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']
        );
        $btnDel = ' ' . $appView->Form->postLink(
            '',
            ['action' => 'delete', $options->id],
            [
                'confirm' => __('Are you sure you want to delete {0}?', $options->{$displayField}),
                'title' => __('Delete'),
                'class' => 'btn btn-default glyphicon glyphicon-trash'
            ]
        );

        $menu = [
            [
                'label' => $btnView,
                'url' => [
                    'plugin' => $request->plugin,
                    'controller' => $request->controller,
                    'action' => 'view',
                    $options->id
                ],
                'capabilities' => 'fromUrl'
            ],
            [
                'label' => $btnEdit,
                'url' => [
                    'plugin' => $request->plugin,
                    'controller' => $request->controller,
                    'action' => 'edit',
                    $options->id
                ],
                'capabilities' => 'fromUrl'
            ],
            [
                'label' => $btnDel,
                'url' => [
                    'plugin' => $request->plugin,
                    'controller' => $request->controller,
                    'action' => 'delete',
                    $options->id
                ],
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

        $model = TableRegistry::get($controllerName);

        $config = $model->getConfig();

        $displayField = $model->displayField();

        /**
         * Conversion logic
         * @todo probably this has to be moved to another plugin
         */
        if (!empty($config['conversion']['modules'])) {
            $btnConvert = ' ' . $appView->Html->link(
                '',
                [
                    'plugin' => null,
                    'controller' => 'conversions',
                    'action' => 'prepare',
                    $controllerName,
                    $options['entity']->id
                ],
                ['title' => __('Convert'), 'class' => 'btn btn-default glyphicon glyphicon-copy']
            );
        }

        $btnChangelog = ' ' . $appView->Html->link(
            '',
            [
                'plugin' => null,
                'controller' => 'log_audit',
                'action' => 'changelog',
                $controllerName,
                $options['entity']->id
            ],
            ['title' => __('Changelog'), 'class' => 'btn btn-default glyphicon glyphicon-book']
        );

        $btnEdit = ' ' . $appView->Html->link(
            '',
            ['action' => 'edit', $options['entity']->id],
            ['title' => __('Edit'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']
        );
        $btnDel = ' ' . $appView->Form->postLink(
            '',
            ['action' => 'delete', $options['entity']->id],
            [
                'confirm' => __('Are you sure you want to delete {0}?', $options['entity']->{$displayField}),
                'title' => __('Delete'),
                'class' => 'btn btn-default glyphicon glyphicon-trash'
            ]
        );

        $menu = [];
        /**
         * Conversion logic
         * @todo probably this has to be moved to another plugin
         */
        if (!empty($config['conversion']['modules'])) {
            $menu[] = [
                'label' => $btnConvert,
                'url' => [
                    'plugin' => null,
                    'controller' => 'conversions',
                    'action' => 'convert',
                    $controllerName,
                    $options['entity']->id
                ],
                'capabilities' => 'fromUrl'
            ];
        }
        $menu[] = [
            'label' => $btnChangelog,
            'url' => [
                'plugin' => null,
                'controller' => 'LogAudit',
                'action' => 'changelog',
                $controllerName,
                $options['entity']->id
            ],
            'capabilities' => 'fromUrl'
        ];
        $menu[] = [
            'label' => $btnEdit,
            'url' => [
                'plugin' => $request->plugin,
                'controller' => $request->controller,
                'action' => 'edit',
                $options['entity']->id
            ],
            'capabilities' => 'fromUrl'
        ];
        $menu[] = [
            'label' => $btnDel,
            'url' => [
                'plugin' => $request->plugin,
                'controller' => $request->controller,
                'action' => 'delete',
                $options['entity']->id
            ],
            'capabilities' => 'fromUrl'
        ];

        $result = null;
        if ($appView->elementExists(static::MENU_ELEMENT)) {
            $result .= $appView->element(static::MENU_ELEMENT, ['menu' => $menu, 'renderAs' => 'provided']);
        } else {
            /**
             * Conversion logic
             * @todo probably this has to be moved to another plugin
             */
            if (!empty($config['conversion']['modules'])) {
                $result .= $btnConvert;
            }

            $result .= $btnChangelog . $btnEdit . $btnDel;
        }

        return $result;
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

        $btnUnlink = $appView->Form->postLink(
            '',
            ['action' => 'unlink', $options['entity']->id, $options['assoc_name'], $options['assoc_entity']->id],
            ['title' => __('Unlink'), 'class' => 'btn btn-default fa fa-chain-broken']
        );

        $menu = [
            [
                'label' => $btnUnlink,
                'url' => [
                    'plugin' => $request->plugin,
                    'controller' => $request->controller,
                    'action' => 'unlink',
                    $options['entity']->id,
                    $options['assoc_name'],
                    $options['assoc_entity']->id
                ],
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

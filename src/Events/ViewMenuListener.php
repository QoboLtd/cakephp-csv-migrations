<?php
namespace CsvMigrations\Events;

use App\View\AppView;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Network\Request;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\View\View;

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
        return [
            'View.View.Menu.Top.Row' => 'getViewMenuTopRow'
        ];
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
}

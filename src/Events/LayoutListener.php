<?php
namespace CsvMigrations\Events;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;

class LayoutListener implements EventListenerInterface
{
    /**
     * Implemented Events
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'QoboAdminPanel.Layout.Head' => 'getMyHead'

        ];
    }

    /**
     * getAllReports method
     * In case we're operating with dynamic CSV tables,
     * we want to overwrite the page title to be used as moduleAlias().
     *
     * @param Cake\Event\Event $event used for getting reports
     * @return void
     */
    public function getMyHead(Event $event)
    {
        $table = TableRegistry::get($event->subject()->request['controller']);
        if ($table) {
            if (method_exists($table, 'moduleAlias') && is_callable([$table, 'moduleAlias'])) {
                $event->subject()->assign('title', $table->moduleAlias());
            }
        }
    }
}

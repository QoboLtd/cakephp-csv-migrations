<?php
namespace CsvMigrations\Events;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use CsvMigrations\MigrationTrait;

class ReportListener implements EventListenerInterface
{
    use MigrationTrait;

    public function implementedEvents()
    {
        return [
            'Search.Report.getReports' => 'getAllReports'
        ];
    }

    public function getAllReports(Event $event) {
        $data = $this->_getReports();
        return $data;
    }
}

<?php
namespace CsvMigrations\Events;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use CsvMigrations\MigrationTrait;

class ReportListener implements EventListenerInterface
{
    use MigrationTrait;

    /**
     * Implemented Events
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Search.Report.getReports' => 'getAllReports'
        ];
    }

    /**
     * getAllReports method
     *
     * @param Cake\Event\Event $event used for getting reports
     *
     * @return array $data with all .ini reports
     */
    public function getAllReports(Event $event)
    {
        $data = $this->getReports();

        return $data;
    }
}

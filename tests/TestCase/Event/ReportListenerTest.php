<?php
namespace CsvMigrations\Test\TestCase\Event;

use Cake\Event\Event;
use Cake\TestSuite\IntegrationTestCase;
use CsvMigrations\Event\ReportListener;

class ReportListenerTest extends IntegrationTestCase
{
    public function testGetAllReports()
    {
        $event = new Event('Search.Report.getReports', $this);
        $listener = new ReportListener();
        // Call the event handler directly, without triggering the actual event
        $result = $listener->getAllReports($event);
        $this->assertTrue(is_array($result), "getAllReports() returned a non-array result");
    }
}

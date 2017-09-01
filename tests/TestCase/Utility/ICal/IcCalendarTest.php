<?php
namespace CsvMigrations\Test\TestCase\Utility\ICal;

use Cake\TestSuite\TestCase;
use CsvMigrations\Utility\ICal\IcCalendar;
use CsvMigrations\Utility\ICal\IcEvent;

/**
 * CsvMigrations\Utility\ICal\IcCalendar Test Case
 */
class IcCalendarTest extends TestCase
{
    public function testGetCalendar()
    {
        $calendar = new IcCalendar();
        $result = $calendar->getCalendar();

        $this->assertTrue(is_object($result), "getCalendar() returned a non-object");
    }

    public function testAddEvent()
    {
        $event = new IcEvent(['summary' => 'foobar']);
        $event = $event->getEvent();

        $calendar = new IcCalendar();
        $calendar->addEvent($event);

        $result = $calendar->getCalendar()->render();

        $this->assertRegExp('/foobar/', $result, "Calendar is missing event summary");
    }
}

<?php
namespace CsvMigrations\Test\TestCase\Utility\ICal;

use Cake\TestSuite\TestCase;
use CsvMigrations\Utility\ICal\IcCalendar;
use CsvMigrations\Utility\ICal\IcEvent;
use DateTime;
use DateTimeZone;

/**
 * CsvMigrations\Utility\ICal\IcEvent Test Case
 */
class IcEventTest extends TestCase
{
    public function testConstructor()
    {
        $event = new IcEvent();
        $result = $event->getEvent();
        $this->assertTrue(is_object($event), "constrctor() failed for null event");

        $event = new IcEvent([]);
        $result = $event->getEvent();
        $this->assertTrue(is_object($event), "constrctor() failed for empty event");

        $event = new IcEvent(['summary' => 'foobar']);
        $result = $event->getEvent();
        $this->assertTrue(is_object($event), "constrctor() failed for non-empty event");

        $event = new IcEvent([]);
        $event = $event->getEvent();
        $event = new IcEvent($event);
        $result = $event->getEvent();
        $this->assertTrue(is_object($event), "constrctor() failed for object event");
    }

    public function testSetId()
    {
        $event = new IcEvent();
        $event->setId('foo123');
        $event = $event->getEvent();
        $result = $event->getUniqueId();
        $this->assertEquals('foo123', $result, "setId() failed to set value");
    }

    public function testSetSequence()
    {
        $event = new IcEvent();
        $event->setSequence('2017');
        $event = $event->getEvent();
        $result = $event->getSequence();
        $this->assertEquals('2017', $result, "setSequence() failed to set value");
    }

    public function testSetStartTime()
    {
        $event = new IcEvent();
        $time = new DateTime(date('Y-m-d H:i:s', strtotime('2017-08-10 18:59:59')), new DateTimeZone('UTC'));
        $event->setStartTime($time);
        $event = $event->getEvent();
        $result = $event->getDtStart();
        $this->assertEquals($result, $time, "setStartTime() failed to set value");
    }

    public function testSetEndTime()
    {
        $event = new IcEvent();
        $time = new DateTime(date('Y-m-d H:i:s', strtotime('2017-08-10 18:59:59')), new DateTimeZone('UTC'));
        $event->setEndTime($time);
        $event = $event->getEvent();
        $result = $event->getDtEnd();
        $this->assertEquals($result, $time, "setEndTime() failed to set value");
    }

    public function testSetAttendees()
    {
        $event = new IcEvent();
        $event->setAttendees(['noone@example.com']);
        $event = $event->getEvent();
        $result = $event->getAttendees();
        $this->assertTrue(is_object($result), "setAttendees() failed to set value");
    }

    public function testSetLocation()
    {
        $event = new IcEvent();
        $event->setLocation("Foobar Ltd");
        $event = $event->getEvent();

        $calendar = new IcCalendar();
        $calendar->addEvent($event);
        $calendar = $calendar->getCalendar();

        $result = $calendar->render();
        $this->assertRegExp('/Foobar Ltd/', $result, "setLocation() failed to set value");
    }

    public function testSetOrganizer()
    {
        $event = new IcEvent();
        $event->setOrganizer("noone@example.com");
        $event = $event->getEvent();

        $calendar = new IcCalendar();
        $calendar->addEvent($event);
        $calendar = $calendar->getCalendar();

        $result = $calendar->render();
        $this->assertRegExp('/noone@example.com/', $result, "setOrganizer() failed to set value");
    }
}

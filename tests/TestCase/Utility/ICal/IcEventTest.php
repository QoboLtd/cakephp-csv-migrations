<?php
namespace CsvMigrations\Test\TestCase\Utility\ICal;

use Cake\TestSuite\TestCase;
use CsvMigrations\Utility\ICal\IcEvent;

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
}

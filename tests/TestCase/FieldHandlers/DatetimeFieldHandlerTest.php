<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\I18n\Time;
use CsvMigrations\FieldHandlers\DatetimeFieldHandler;
use PHPUnit_Framework_TestCase;

class DatetimeFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $fh;

    protected function setUp()
    {
        $this->fh = new DatetimeFieldHandler();
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->fh));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\FieldHandlerInterface', $implementedInterfaces), "FieldHandlerInterface is not implemented");
    }

    public function getValues()
    {
        return [
            ['2017-07-06 14:20:00', '2017-07-06 14:20:00', 'Date time string'],
            ['2017-07-06', '2017-07-06', 'Date string'],
            ['14:20:00', '14:20:00', 'Time string'],
            ['foobar', 'foobar', 'Non-date string'],
            [15, 15, 'Non-date integer'],
            [Time::parse('2017-07-06 14:20:00'), '2017-07-06 14:20', 'Date time from object'],
        ];
    }

    /**
     * @dataProvider getValues
     */
    public function testRenderValue($value, $expected, $description)
    {
        $result = $this->fh->renderValue(null, null, $value, []);
        $this->assertEquals($expected, $result, "Value rendering is broken for: $description");
    }
}

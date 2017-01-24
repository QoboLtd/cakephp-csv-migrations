<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\I18n\Time;
use CsvMigrations\FieldHandlers\DateFieldHandler;
use PHPUnit_Framework_TestCase;

class DateFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $fh;

    protected function setUp()
    {
        $this->fh = new DateFieldHandler('fields', 'field_date');
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
            [Time::parse('2017-07-06 14:20:00'), '2017-07-06', 'Date from object'],
        ];
    }

    /**
     * @dataProvider getValues
     */
    public function testRenderValue($value, $expected, $description)
    {
        $result = $this->fh->renderValue($value, []);
        $this->assertEquals($expected, $result, "Value rendering is broken for: $description");
    }

    public function testRenderInput()
    {
        $result = $this->fh->renderInput('2016-10-15');
        $this->assertRegExp('/field_date/', $result, "Input rendering does not contain field name");
    }

    public function testRenderSearchInput()
    {
        $result = $this->fh->renderSearchInput();
        $this->assertTrue(is_array($result));
        $this->assertFalse(empty($result));
    }

    public function testGetSearchOperators()
    {
        $result = $this->fh->getSearchOperators();
        $this->assertTrue(is_array($result), "getSearchOperators() did not return an array");
        $this->assertFalse(empty($result), "getSearchOperators() returned an empty result");
        $this->assertArrayHasKey('is', $result, "getSearchOperators() did not return 'is' key");
        $this->assertArrayHasKey('is_not', $result, "getSearchOperators() did not return 'is_not' key");
        $this->assertArrayHasKey('greater', $result, "getSearchOperators() did not return 'greater' key");
        $this->assertArrayHasKey('less', $result, "getSearchOperators() did not return 'less' key");
    }
}

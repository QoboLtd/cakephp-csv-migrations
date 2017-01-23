<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\PhoneFieldHandler;
use PHPUnit_Framework_TestCase;

class PhoneFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $fh;

    protected function setUp()
    {
        $this->fh = new PhoneFieldHandler('fields', 'field_phone');
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->fh));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\FieldHandlerInterface', $implementedInterfaces), "FieldHandlerInterface is not implemented");
    }

    public function getValues()
    {
        return [
            [true, true, 'Boolean true'],
            [false, false, 'Boolean false'],
            [0, '0', 'Integer zero'],
            [1, '1', 'Positive integer'],
            [-1, '-1', 'Negative integer'],
            [1.501, '1.501', 'Positive float'],
            [-1.501, '-1.501', 'Negative float'],
            ['', '', 'Empty string'],
            ['+35799000000', '+35799000000', 'String'],
            ['2017-07-05', '2017-07-05', 'Date'],
            ['www.google.com', 'www.google.com', 'URL without schema'],
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

    public function testRenderValueWithPlainFlag()
    {
        $result = $this->fh->renderValue('http://www.google.com', ['renderAs' => 'plain']);
        $this->assertEquals('http://www.google.com', $result);
    }

    public function testGetSearchOperators()
    {
        $result = $this->fh->getSearchOperators();
        $this->assertTrue(is_array($result), "getSearchOperators() did not return an array");
        $this->assertFalse(empty($result), "getSearchOperators() returned an empty result");
        $this->assertArrayHasKey('contains', $result, "getSearchOperators() did not return 'contains' key");
        $this->assertArrayHasKey('not_contains', $result, "getSearchOperators() did not return 'not_contains' key");
        $this->assertArrayHasKey('starts_with', $result, "getSearchOperators() did not return 'starts_with' key");
        $this->assertArrayHasKey('ends_with', $result, "getSearchOperators() did not return 'ends_with' key");
    }
}

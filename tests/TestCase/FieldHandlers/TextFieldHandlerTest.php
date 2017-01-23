<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\TextFieldHandler;
use PHPUnit_Framework_TestCase;

class TextFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $fh;

    protected function setUp()
    {
        $this->fh = new TextFieldHandler('fields', 'field_text');
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->fh));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\FieldHandlerInterface', $implementedInterfaces), "FieldHandlerInterface is not implemented");
    }

    public function getValues()
    {
        return [
            [true, "<p>1</p>\n", 'Boolean true'],
            [false, '', 'Boolean false'],
            [0, 0, 'Integer zero'],
            [1, "<p>1</p>\n", 'Positive integer'],
            [-1, "<p>-1</p>\n", 'Negative integer'],
            [1.501, "<p>1.501</p>\n", 'Positive float'],
            [-1.501, "<p>-1.501</p>\n", 'Negative float'],
            ['', '', 'Empty string'],
            ['foobar', "<p>foobar</p>\n", 'String'],
            ['2017-07-05', "<p>2017-07-05</p>\n", 'Date'],
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
        $result = $this->fh->renderValue('Hello World!', ['renderAs' => 'plain']);
        $this->assertEquals('Hello World!', $result);
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

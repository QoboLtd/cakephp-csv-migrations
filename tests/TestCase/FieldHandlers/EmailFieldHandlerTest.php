<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\EmailFieldHandler;
use PHPUnit_Framework_TestCase;

class EmailFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $fh;

    protected function setUp()
    {
        $this->fh = new EmailFieldHandler();
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
            ['foobar', 'foobar', 'String'],
            ['2017-07-05', '2017-07-05', 'Date'],
            ['user@example.com', '<a href="mailto:user@example.com" target="_blank">user@example.com</a>', 'Email'],
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

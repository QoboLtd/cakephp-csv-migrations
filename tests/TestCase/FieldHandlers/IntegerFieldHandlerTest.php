<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\IntegerFieldHandler;
use PHPUnit_Framework_TestCase;

class IntegerFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $fh;

    protected function setUp()
    {
        $this->fh = new IntegerFieldHandler();
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->fh));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\FieldHandlerInterface', $implementedInterfaces), "FieldHandlerInterface is not implemented");
    }

    public function getValues()
    {
        return [
            [true, '1', 'Boolean true'],
            [false, '', 'Boolean false'],
            [0, '0', 'Integer zero'],
            [1, '1', 'Positive integer'],
            [-1, '-1', 'Negative integer'],
            ['', '', 'Empty string'],
            ['foobar', '', 'String'],
            ['foobar15', '15', 'String with number'],
            ['2017-07-05', '2017-07-05', 'Date'],
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

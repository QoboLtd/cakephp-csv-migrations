<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\DefaultFieldHandler;
use PHPUnit_Framework_TestCase;

class DefaultFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $table = 'Fields';
    protected $field = 'field_string';

    protected $fh;

    protected function setUp()
    {
        $this->fh = new DefaultFieldHandler($this->table, $this->field);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->fh));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\FieldHandlerInterface', $implementedInterfaces), "FieldHandlerInterface is not implemented");
    }

    public function getValues()
    {
        return [
            [true, 'Boolean true'],
            [false, 'Boolean false'],
            [0, 'Integer zero'],
            [1, 'Positive integer'],
            [-1, 'Negative integer'],
            [1.501, 'Positive float'],
            [-1.501, 'Negative float'],
            ['', 'Empty string'],
            ['foobar', 'String'],
            ['2017-07-05', 'Date'],
        ];
    }

    /**
     * @dataProvider getValues
     */
    public function testRenderValue($value, $description)
    {
        $result = $this->fh->renderValue($value, []);
        $this->assertEquals($value, $result, "Value rendering is broken for: $description");
    }
}

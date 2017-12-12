<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Renderer\Value;

use CsvMigrations\FieldHandlers\Renderer\Value\IntegerRenderer;
use PHPUnit_Framework_TestCase;
use StdClass;

class IntegerRendererTest extends PHPUnit_Framework_TestCase
{
    protected $renderer;

    protected function setUp()
    {
        $this->renderer = new IntegerRenderer();
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->renderer));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Renderer\Value\RendererInterface', $implementedInterfaces), "RendererInterface is not implemented");
    }

    public function getValues()
    {
        return [
            [true, '1', 'Boolean true'],
            [false, '0', 'Boolean false'],
            [0, '0', 'Integer zero'],
            [1, '1', 'Positive integer'],
            [-1, '-1', 'Negative integer'],
            ['', '0', 'Empty string'],
            ['foobar', '0', 'String'],
            ['foobar15', '15', 'String with number'],
            ['2017-07-05', '2,017', 'Date'],
        ];
    }

    /**
     * @dataProvider getValues
     */
    public function testRenderValue($value, $expected, $description)
    {
        $result = $this->renderer->renderValue($value);
        $this->assertEquals($expected, $result, "Value rendering is broken for: $description");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderValueException()
    {
        $result = $this->renderer->renderValue(new StdClass());
    }
}

<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Renderer\Value;

use CsvMigrations\FieldHandlers\Renderer\Value\DecimalRenderer;
use PHPUnit_Framework_TestCase;
use StdClass;

class DecimalRendererTest extends PHPUnit_Framework_TestCase
{
    protected $renderer;

    protected function setUp()
    {
        $this->renderer = new DecimalRenderer();
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->renderer));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Renderer\Value\RendererInterface', $implementedInterfaces), "RendererInterface is not implemented");
    }

    public function getValues()
    {
        return [
            [true, '1.00', 'Boolean true'],
            [false, '0', 'Boolean false'],
            [0, '0', 'Integer zero'],
            [1, '1.00', 'Positive integer'],
            [-1, '-1.00', 'Negative integer'],
            [1.50, '1.50', 'Positive float'],
            [-1.50, '-1.50', 'Negative float'],
            ['', '0', 'Empty string'],
            ['foobar', '0', 'String'],
            ['foobar15', '15', 'String with number'],
            ['2017-07-05', '2,017.00', 'Date'],
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

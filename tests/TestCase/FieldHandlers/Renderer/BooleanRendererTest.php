<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Renderer;

use CsvMigrations\FieldHandlers\Renderer\BooleanRenderer;
use PHPUnit_Framework_TestCase;

class BooleanRendererTest extends PHPUnit_Framework_TestCase
{
    protected $renderer;

    protected function setUp()
    {
        $this->renderer = new BooleanRenderer();
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->renderer));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Renderer\RendererInterface', $implementedInterfaces), "RendererInterface is not implemented");
    }

    public function getValues()
    {
        return [
            [null, '0', 'Null'],
            ['', '0', 'Empty string'],
            [1, '1', 'Integer true'],
            [0, '0', 'Integer false'],
            ['1', '1', 'String true'],
            ['0', '0', 'String false'],
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

    public function testRenderValueLabels()
    {
        $valueLabels = [
            0 => 'Nope',
            1 => 'Yup',
        ];

        $result = $this->renderer->renderValue(false, ['valueLabels' => $valueLabels]);
        $this->assertEquals('Nope', $result, "Value rendering is broken for false with custom labels");

        $result = $this->renderer->renderValue(true, ['valueLabels' => $valueLabels]);
        $this->assertEquals('Yup', $result, "Value rendering is broken for true with custom labels");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderValueLabelsException()
    {
        $result = $this->renderer->renderValue(false, ['valueLabels' => 'this_is_not_an_array']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderValueLabelsCountException()
    {
        $result = $this->renderer->renderValue(false, ['valueLabels' => ['not_enough_labels']]);
    }
}

<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Renderer;

use CsvMigrations\FieldHandlers\Renderer\BooleanOnOffRenderer;
use PHPUnit_Framework_TestCase;

class BooleanOnOffRendererTest extends PHPUnit_Framework_TestCase
{
    protected $renderer;

    protected function setUp()
    {
        $this->renderer = new BooleanOnOffRenderer();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->renderer);

        parent::tearDown();
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->renderer));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Renderer\RendererInterface', $implementedInterfaces), "RendererInterface is not implemented");
    }

    public function getValues()
    {
        return [
            [null, 'Off', 'Null'],
            ['', 'Off', 'Empty string'],
            [1, 'On', 'Integer true'],
            [0, 'Off', 'Integer false'],
            ['1', 'On', 'String true'],
            ['0', 'Off', 'String false'],
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
}

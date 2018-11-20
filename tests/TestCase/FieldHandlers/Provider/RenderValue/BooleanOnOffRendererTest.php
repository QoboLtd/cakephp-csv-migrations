<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Config\BooleanConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\BooleanOnOffRenderer;
use PHPUnit\Framework\TestCase;

class BooleanOnOffRendererTest extends TestCase
{
    protected $renderer;

    protected function setUp() : void
    {
        $config = new BooleanConfig('boolean');
        $this->renderer = new BooleanOnOffRenderer($config);
    }

    public function testInterface() : void
    {
        $implementedInterfaces = array_keys(class_implements($this->renderer));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    /**
     * @return mixed[]
     */
    public function getValues() : array
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
     * @param mixed $value
     * @param mixed $expected
     */
    public function testRenderValue($value, $expected, string $description) : void
    {
        $result = $this->renderer->provide($value);
        $this->assertEquals($expected, $result, "Value rendering is broken for: $description");
    }

    public function testRenderValueLabels() : void
    {
        $valueLabels = [
            0 => 'Nope',
            1 => 'Yup',
        ];

        $result = $this->renderer->provide(false, ['valueLabels' => $valueLabels]);
        $this->assertEquals('Nope', $result, "Value rendering is broken for false with custom labels");

        $result = $this->renderer->provide(true, ['valueLabels' => $valueLabels]);
        $this->assertEquals('Yup', $result, "Value rendering is broken for true with custom labels");
    }
}

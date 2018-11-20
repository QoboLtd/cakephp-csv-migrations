<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Config\IntegerConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\IntegerRenderer;
use PHPUnit\Framework\TestCase;
use stdClass;

class IntegerRendererTest extends TestCase
{
    protected $renderer;

    protected function setUp() : void
    {
        $config = new IntegerConfig('integer');
        $this->renderer = new IntegerRenderer($config);
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
     * @param mixed $value
     */
    public function testRenderValue($value, string $expected, string $description) : void
    {
        $result = $this->renderer->provide($value);
        $this->assertEquals($expected, $result, "Value rendering is broken for: $description");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderValueException() : void
    {
        $result = $this->renderer->provide(new stdClass());
    }
}

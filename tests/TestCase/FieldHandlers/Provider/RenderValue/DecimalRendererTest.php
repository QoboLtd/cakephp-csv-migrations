<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Config\DecimalConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\DecimalRenderer;
use PHPUnit\Framework\TestCase;
use stdClass;

class DecimalRendererTest extends TestCase
{
    protected $renderer;

    protected function setUp(): void
    {
        $config = new DecimalConfig('decimal');
        $this->renderer = new DecimalRenderer($config);
    }

    public function testInterface(): void
    {
        $implementedInterfaces = array_keys(class_implements($this->renderer));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    /**
     * @return mixed[]
     */
    public function getValues(): array
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
     * @param mixed $value
     * @param mixed $expected
     */
    public function testRenderValue($value, $expected, string $description): void
    {
        $result = $this->renderer->provide($value);
        $this->assertEquals($expected, $result, "Value rendering is broken for: $description");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderValueException(): void
    {
        $result = $this->renderer->provide(new stdClass());
    }
}

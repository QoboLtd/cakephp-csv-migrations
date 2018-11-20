<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Config\StringConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\StringRenderer;
use PHPUnit\Framework\TestCase;

class StringRendererTest extends TestCase
{
    protected $renderer;

    protected function setUp() : void
    {
        $config = new StringConfig('string');
        $this->renderer = new StringRenderer($config);
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
            [false, '', 'Boolean false'],
            [0, '0', 'Integer zero'],
            [1, '1', 'Positive integer'],
            [-1, '-1', 'Negative integer'],
            [1.501, '1.501', 'Positive float'],
            [-1.501, '-1.501', 'Negative float'],
            ['', '', 'Empty string'],
            ['foobar', 'foobar', 'String'],
            ['2017-07-05', '2017-07-05', 'Date'],
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
}

<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Config\TextConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\TextRenderer;
use PHPUnit\Framework\TestCase;

class TextRendererTest extends TestCase
{
    protected $renderer;

    protected function setUp() : void
    {
        $config = new TextConfig('text');
        $this->renderer = new TextRenderer($config);
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
            [true, "<p>1</p>\n", 'Boolean true'],
            [false, '', 'Boolean false'],
            [0, 0, 'Integer zero'],
            [1, "<p>1</p>\n", 'Positive integer'],
            [-1, "<p>-1</p>\n", 'Negative integer'],
            [1.501, "<p>1.501</p>\n", 'Positive float'],
            [-1.501, "<p>-1.501</p>\n", 'Negative float'],
            ['', '', 'Empty string'],
            ['foobar', "<p>foobar</p>\n", 'String'],
            ['2017-07-05', "<p>2017-07-05</p>\n", 'Date'],
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
}

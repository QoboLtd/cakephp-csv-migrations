<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Config\TextConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\TextRenderer;
use PHPUnit_Framework_TestCase;

class TextRendererTest extends PHPUnit_Framework_TestCase
{
    protected $renderer;

    protected function setUp()
    {
        $config = new TextConfig('text');
        $this->renderer = new TextRenderer($config);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->renderer));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    public function getValues()
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
     */
    public function testRenderValue($value, $expected, $description)
    {
        $result = $this->renderer->provide($value);
        $this->assertEquals($expected, $result, "Value rendering is broken for: $description");
    }
}

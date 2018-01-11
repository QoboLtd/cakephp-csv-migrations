<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Config\UrlConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\LinkRenderer;
use PHPUnit_Framework_TestCase;

class LinkRendererTest extends PHPUnit_Framework_TestCase
{
    protected $renderer;

    protected function setUp()
    {
        $config = new UrlConfig('link');
        $this->renderer = new LinkRenderer($config);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->renderer));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    public function getValues()
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
            ['www.google.com', 'www.google.com', 'URL without schema'],
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

    public function testRenderValueWithOptions()
    {
        // Simple
        $options = [
            'linkTo' => 'http://example.com/%s/',
        ];
        $result = $this->renderer->provide(123, $options);
        $expected = '<a href="http://example.com/123/" target="_blank">123</a>';
        $this->assertEquals($expected, $result, "Value rendering is broken for simple value with option");

        // Encoded
        $options = [
            'linkTo' => 'http://example.com/%s/',
            'linkTarget' => 'foobar',
        ];
        $result = $this->renderer->provide('123&456', $options);
        $expected = '<a href="http://example.com/123%26456/" target="foobar">123&amp;456</a>';
        $this->assertEquals($expected, $result, "Value rendering is broken for encoded value with option");
    }
}

<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Config\UrlConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\UrlRenderer;
use PHPUnit\Framework\TestCase;

class UrlRendererTest extends TestCase
{
    protected $renderer;

    protected function setUp(): void
    {
        $config = new UrlConfig('url');
        $this->renderer = new UrlRenderer($config);
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
            ['http://www.google.com', '<a href="http://www.google.com" target="_blank">http://www.google.com</a>', 'URL with schema'],
            [[], '', 'Array Value'],
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
        $this->assertSame($expected, $result, "Value rendering is broken for: $description");
    }

    public function testRenderValueWithOptions(): void
    {
        // Simple
        $options = [
            'linkTarget' => 'foobar',
        ];
        $result = $this->renderer->provide('http://www.google.com', $options);
        $expected = '<a href="http://www.google.com" target="foobar">http://www.google.com</a>';
        $this->assertEquals($expected, $result, "Value rendering is broken for simple value with option");
    }
}

<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\Config\UrlConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\LinkRenderer;
use PHPUnit\Framework\TestCase;

class LinkRendererTest extends TestCase
{
    protected $renderer;

    protected function setUp(): void
    {
        $config = new UrlConfig('link');
        $this->renderer = new LinkRenderer($config);
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
            ['/foobar', '/foobar', 'Relative URL that starts with a slash'],
            [[], '', 'Array Value'],
        ];
    }

    /**
     * @dataProvider getValues
     * @param mixed $value
     */
    public function testRenderValue($value, string $expected, string $description): void
    {
        $result = $this->renderer->provide($value);
        $this->assertSame($expected, $result, "Value rendering is broken for: $description");
    }

    public function testRenderValueWithOptions(): void
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

        $fullBaseUrl = Configure::read('App.fullBaseUrl');
        // set custom full base url
        Configure::write('App.fullBaseUrl', 'http://example.com');

        // Relative
        $this->assertEquals(
            '<a href="http://example.com/123/" target="_blank">123</a>',
            $this->renderer->provide(123, ['linkTo' => '/%s/']),
            'Value rendering is broken for relative value with option'
        );

        // restore original full base url value
        Configure::write('App.fullBaseUrl', $fullBaseUrl);
    }
}

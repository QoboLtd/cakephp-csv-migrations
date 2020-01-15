<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Config\CountryConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\CountryRenderer;
use PHPUnit\Framework\TestCase;

class CountryRendererTest extends TestCase
{
    protected $renderer;

    protected function setUp(): void
    {
        $config = new CountryConfig('list');
        $this->renderer = new CountryRenderer($config);
    }

    /**
     * @return mixed[]
     */
    public function basicValues(): array
    {
        return [
            ['cy', 'Cyprus'],
        ];
    }

    /**
     * @dataProvider basicValues
     */
    public function testRenderValue(string $value, string $label): void
    {
        $result = $this->renderer->provide($value, ['listItems' => [$value => $label]]);
        $expected = sprintf(CountryRenderer::ICON_HTML, strtolower($value), $label);

        $this->assertEquals($expected, $result, "Value rendering is broken");
    }

    /**
     * @dataProvider basicValues
     */
    public function testRenderValueNotFound(string $value, string $label): void
    {
        $result = $this->renderer->provide('ru', ['listItems' => [$value => $label]]);
        $expected = sprintf(CountryRenderer::VALUE_NOT_FOUND_HTML, 'ru');

        $this->assertEquals($expected, $result, "Value rendering is broken for missing text value");
    }
}

<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use Cake\I18n\Time;
use CsvMigrations\FieldHandlers\Config\TimeConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\TimeRenderer;
use PHPUnit\Framework\TestCase;
use stdClass;

class TimeRendererTest extends TestCase
{
    protected $renderer;

    protected function setUp(): void
    {
        $config = new TimeConfig('time');
        $this->renderer = new TimeRenderer($config);
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
            ['2017-07-06 14:20:00', '2017-07-06 14:20:00', 'Date time string'],
            ['2017-07-06', '2017-07-06', 'Date string'],
            ['14:20:00', '14:20:00', 'Time string'],
            ['foobar', 'foobar', 'Non-date string'],
            [15, '15', 'Non-date integer'],
            [null, '', 'Null'],
            [Time::parse('2017-07-06 14:20:00'), '14:20', 'Time from object'],
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

    public function testRenderValueFormat(): void
    {
        $result = $this->renderer->provide(Time::parse('2017-07-06 14:20:00'), ['format' => 'yyyy']);
        $this->assertEquals('2017', $result, "Value rendering is broken for custom format");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderValueException(): void
    {
        $result = $this->renderer->provide(new stdClass());
    }
}

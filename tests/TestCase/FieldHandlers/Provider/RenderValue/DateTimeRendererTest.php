<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use Cake\I18n\Time;
use CsvMigrations\FieldHandlers\Config\DatetimeConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\DatetimeRenderer;
use PHPUnit\Framework\TestCase;
use stdClass;

class DateTimeRendererTest extends TestCase
{
    protected $renderer;

    protected function setUp() : void
    {
        $config = new DatetimeConfig('datetime');
        $this->renderer = new DatetimeRenderer($config);
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
            ['2017-07-06 14:20:00', '2017-07-06 14:20:00', 'Date time string'],
            ['2017-07-06', '2017-07-06', 'Date string'],
            ['14:20:00', '14:20:00', 'Time string'],
            ['foobar', 'foobar', 'Non-date string'],
            [15, '15', 'Non-date integer'],
            [null, '', 'Null'],
            [Time::parse('2017-07-06 14:20:00'), '2017-07-06 14:20', 'Date time from object'],
            [[], '', 'Array Value'],
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
        $this->assertSame($expected, $result, "Value rendering is broken for: $description");
    }

    public function testRenderValueFormat() : void
    {
        $result = $this->renderer->provide(Time::parse('2017-07-06 14:20:00'), ['format' => 'yyyy']);
        $this->assertEquals('2017', $result, "Value rendering is broken for custom format");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderValueException() : void
    {
        $result = $this->renderer->provide(new stdClass());
    }
}

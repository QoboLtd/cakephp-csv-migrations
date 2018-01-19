<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use Cake\I18n\Time;
use CsvMigrations\FieldHandlers\Config\TimeConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\TimeRenderer;
use PHPUnit_Framework_TestCase;
use StdClass;

class TimeRendererTest extends PHPUnit_Framework_TestCase
{
    protected $renderer;

    protected function setUp()
    {
        $config = new TimeConfig('time');
        $this->renderer = new TimeRenderer($config);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->renderer));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    public function getValues()
    {
        return [
            ['2017-07-06 14:20:00', '2017-07-06 14:20:00', 'Date time string'],
            ['2017-07-06', '2017-07-06', 'Date string'],
            ['14:20:00', '14:20:00', 'Time string'],
            ['foobar', 'foobar', 'Non-date string'],
            [15, '15', 'Non-date integer'],
            [null, '', 'Null'],
            [Time::parse('2017-07-06 14:20:00'), '14:20', 'Time from object'],
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

    public function testRenderValueFormat()
    {
        $result = $this->renderer->provide(Time::parse('2017-07-06 14:20:00'), ['format' => 'yyyy']);
        $this->assertEquals('2017', $result, "Value rendering is broken for custom format");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderValueException()
    {
        $result = $this->renderer->provide(new StdClass());
    }
}

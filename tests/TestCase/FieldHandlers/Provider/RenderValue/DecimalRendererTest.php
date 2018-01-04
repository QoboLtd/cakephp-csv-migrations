<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Config\DecimalConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\DecimalRenderer;
use PHPUnit_Framework_TestCase;
use StdClass;

class DecimalRendererTest extends PHPUnit_Framework_TestCase
{
    protected $renderer;

    protected function setUp()
    {
        $config = new DecimalConfig('decimal');
        $this->renderer = new DecimalRenderer($config);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->renderer));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    public function getValues()
    {
        return [
            [true, '1.00', 'Boolean true'],
            [false, '0', 'Boolean false'],
            [0, '0', 'Integer zero'],
            [1, '1.00', 'Positive integer'],
            [-1, '-1.00', 'Negative integer'],
            [1.50, '1.50', 'Positive float'],
            [-1.50, '-1.50', 'Negative float'],
            ['', '0', 'Empty string'],
            ['foobar', '0', 'String'],
            ['foobar15', '15', 'String with number'],
            ['2017-07-05', '2,017.00', 'Date'],
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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderValueException()
    {
        $result = $this->renderer->provide(new StdClass());
    }
}

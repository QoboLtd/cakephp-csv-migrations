<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Config\EmailConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\EmailRenderer;
use PHPUnit\Framework\TestCase;

class EmailRendererTest extends TestCase
{
    protected $renderer;

    protected function setUp() : void
    {
        $config = new EmailConfig('email');
        $this->renderer = new EmailRenderer($config);
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
            [true, true, 'Boolean true'],
            [false, false, 'Boolean false'],
            [0, '0', 'Integer zero'],
            [1, '1', 'Positive integer'],
            [-1, '-1', 'Negative integer'],
            [1.501, '1.501', 'Positive float'],
            [-1.501, '-1.501', 'Negative float'],
            ['', '', 'Empty string'],
            ['foobar', 'foobar', 'String'],
            ['2017-07-05', '2017-07-05', 'Date'],
            ['user@example.com', '<a href="mailto:user@example.com" target="_blank">user@example.com</a>', 'Email'],
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

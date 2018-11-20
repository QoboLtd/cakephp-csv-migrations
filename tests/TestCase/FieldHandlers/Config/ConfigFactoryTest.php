<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Config;

use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use CsvMigrations\FieldHandlers\Config\ConfigInterface;
use PHPUnit\Framework\TestCase;

class ConfigFactoryTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function supportedTypesProvider() : array
    {
        return [
            ['blob'],
            ['boolean'],
            ['date'],
            ['datetime'],
            ['dblist'],
            ['decimal'],
            ['email'],
            ['files'],
            ['hasMany'],
            ['integer'],
            ['list'],
            ['metric'],
            ['money'],
            ['phone'],
            ['related'],
            ['reminder'],
            ['string'],
            ['sublist'],
            ['text'],
            ['time'],
            ['url'],
            ['uuid'],
        ];
    }

    /**
     * @dataProvider supportedTypesProvider
     */
    public function testGetByType(string $type) : void
    {
        $field = 'foo';
        $result = ConfigFactory::getByType($type, $field);
        $this->assertTrue(is_object($result), "ConfigFactory returned a non-object result");
        $this->assertTrue($result instanceof ConfigInterface, "ConfigFactory returned invalid interface instance");
        $this->assertEquals($field, $result->getField(), "Returned config instance does not have correct field name");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetByTypeException() : void
    {
        $result = ConfigFactory::getByType('unsupported_type', 'foo');
    }
}

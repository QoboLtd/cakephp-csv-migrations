<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\FieldToDb;

use CsvMigrations\FieldHandlers\Config\StringConfig;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DbField;
use CsvMigrations\FieldHandlers\Provider\FieldToDb\TimeFieldToDb;
use PHPUnit\Framework\TestCase;

class TimeFieldToDbTest extends TestCase
{
    protected $provider;

    protected function setUp(): void
    {
        $config = new StringConfig('foobar');
        $this->provider = new TimeFieldToDb($config);
    }

    public function testInterface(): void
    {
        $implementedInterfaces = array_keys(class_implements($this->provider));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    public function testProvide(): void
    {
        $csvField = new CsvField(['name' => 'foobar']);
        $result = $this->provider->provide($csvField);
        $this->assertTrue(is_array($result), "Provider returned a non-array result");
        $this->assertFalse(empty($result), "Provider returned an empty result");

        $this->assertEquals(1, count(array_keys($result)), "Provider returned incorrect number of fields");
        $this->assertTrue(array_key_exists('foobar', $result), "Given field is missing from the result");
        $this->assertTrue(is_object($result['foobar']), "Given field result is not an object");
        $this->assertTrue($result['foobar'] instanceof DbField, "Given field is not an instance of DbField");

        $this->assertEquals('foobar', $result['foobar']->getName(), "DbField name is incorrect");
        $this->assertEquals('time', $result['foobar']->getType(), "DbField type is incorrect");
    }

    /**
     * @return mixed[]
     */
    public function invalidDataProvider(): array
    {
        return [
            [null],
            [true],
            [100],
            ['foobar'],
            [['one' => 'two']],
            [new \stdClass()],
        ];
    }

    /**
     * @param mixed $data
     * @dataProvider invalidDataProvider
     * @expectedException \InvalidArgumentException
     */
    public function testProvideException($data): void
    {
        $result = $this->provider->provide($data);
    }
}

<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\FieldToDb;

use CsvMigrations\FieldHandlers\Config\MoneyConfig;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DbField;
use CsvMigrations\FieldHandlers\Provider\FieldToDb\CombinedFieldToDb;
use PHPUnit_Framework_TestCase;

class CombinedFieldToDbTest extends PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $config = new MoneyConfig('foobar');
        $this->provider = new CombinedFieldToDb($config);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->provider));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    public function testProvide()
    {
        $csvField = new CsvField(['name' => 'foobar']);
        $result = $this->provider->provide($csvField);
        $this->assertTrue(is_array($result), "Provider returned a non-array result");
        $this->assertFalse(empty($result), "Provider returned an empty result");

        $this->assertEquals(2, count(array_keys($result)), "Provider returned incorrect number of fields");
        $this->assertTrue(array_key_exists('foobar_amount', $result), "Given field is missing from the result");
        $this->assertTrue(is_object($result['foobar_amount']), "Given field result is not an object");
        $this->assertTrue($result['foobar_amount'] instanceof DbField, "Given field is not an instance of DbField");
        $this->assertEquals('foobar_amount', $result['foobar_amount']->getName(), "DbField name is incorrect");

        $this->assertTrue(array_key_exists('foobar_currency', $result), "Given field is missing from the result");
        $this->assertTrue(is_object($result['foobar_currency']), "Given field result is not an object");
        $this->assertTrue($result['foobar_currency'] instanceof DbField, "Given field is not an instance of DbField");
        $this->assertEquals('foobar_currency', $result['foobar_currency']->getName(), "DbField name is incorrect");
    }

    public function invalidDataProvider()
    {
        return [
            [null],
            [true],
            [100],
            ['foobar'],
            [['one' => 'two']],
            [new \StdClass()],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     * @expectedException \InvalidArgumentException
     */
    public function testProvideException($data)
    {
        $result = $this->provider->provide($data);
    }
}

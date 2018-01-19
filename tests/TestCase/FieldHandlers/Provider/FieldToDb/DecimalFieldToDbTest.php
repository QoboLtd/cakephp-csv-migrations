<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\FieldToDb;

use CsvMigrations\FieldHandlers\Config\StringConfig;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DbField;
use CsvMigrations\FieldHandlers\Provider\FieldToDb\DecimalFieldToDb;
use PHPUnit_Framework_TestCase;

class DecimalFieldToDbTest extends PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $config = new StringConfig('foobar');
        $this->provider = new DecimalFieldToDb($config);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->provider));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    public function testProvide()
    {
        // Defaults
        $csvField = new CsvField(['name' => 'foobar']);
        $result = $this->provider->provide($csvField);
        $this->assertTrue(is_array($result), "Provider returned a non-array result");
        $this->assertFalse(empty($result), "Provider returned an empty result");

        $this->assertEquals(1, count(array_keys($result)), "Provider returned incorrect number of fields");
        $this->assertTrue(array_key_exists('foobar', $result), "Given field is missing from the result");
        $this->assertTrue(is_object($result['foobar']), "Given field result is not an object");
        $this->assertTrue($result['foobar'] instanceof DbField, "Given field is not an instance of DbField");

        $this->assertEquals('foobar', $result['foobar']->getName(), "DbField name is incorrect");
        $this->assertEquals('decimal', $result['foobar']->getType(), "DbField type is incorrect");
        $this->assertEquals(null, $result['foobar']->getLimit(), "DbField limit is incorrect");

        $options = $result['foobar']->getOptions();
        $this->assertTrue(array_key_exists('precision', $options), "Field options are 'precision' key");
        $this->assertEquals(10, $options['precision'], "Field options provided wrong value for 'precision' key");
        $this->assertTrue(array_key_exists('scale', $options), "Field options are 'scale' key");
        $this->assertEquals(2, $options['scale'], "Field options provided wrong value for 'scale' key");

        // Partial limit (does not affect anything)
        $csvField = new CsvField(['name' => 'foobar', 'type' => 'decimal(5)']);
        $result = $this->provider->provide($csvField);
        $this->assertTrue(is_array($result), "Provider returned a non-array result");
        $this->assertFalse(empty($result), "Provider returned an empty result");

        $this->assertEquals(1, count(array_keys($result)), "Provider returned incorrect number of fields");
        $this->assertTrue(array_key_exists('foobar', $result), "Given field is missing from the result");
        $this->assertTrue(is_object($result['foobar']), "Given field result is not an object");
        $this->assertTrue($result['foobar'] instanceof DbField, "Given field is not an instance of DbField");

        $this->assertEquals('foobar', $result['foobar']->getName(), "DbField name is incorrect");
        $this->assertEquals('decimal', $result['foobar']->getType(), "DbField type is incorrect");
        $this->assertEquals(5, $result['foobar']->getLimit(), "DbField limit is incorrect");

        $options = $result['foobar']->getOptions();
        $this->assertTrue(array_key_exists('precision', $options), "Field options are 'precision' key");
        $this->assertEquals(10, $options['precision'], "Field options provided wrong value for 'precision' key");
        $this->assertTrue(array_key_exists('scale', $options), "Field options are 'scale' key");
        $this->assertEquals(2, $options['scale'], "Field options provided wrong value for 'scale' key");

        // Proper limit
        $csvField = new CsvField(['name' => 'foobar', 'type' => 'decimal(5.6)']);
        $result = $this->provider->provide($csvField);
        $this->assertTrue(is_array($result), "Provider returned a non-array result");
        $this->assertFalse(empty($result), "Provider returned an empty result");

        $this->assertEquals(1, count(array_keys($result)), "Provider returned incorrect number of fields");
        $this->assertTrue(array_key_exists('foobar', $result), "Given field is missing from the result");
        $this->assertTrue(is_object($result['foobar']), "Given field result is not an object");
        $this->assertTrue($result['foobar'] instanceof DbField, "Given field is not an instance of DbField");

        $this->assertEquals('foobar', $result['foobar']->getName(), "DbField name is incorrect");
        $this->assertEquals('decimal', $result['foobar']->getType(), "DbField type is incorrect");
        $this->assertEquals('5.6', $result['foobar']->getLimit(), "DbField limit is incorrect");

        $options = $result['foobar']->getOptions();
        $this->assertTrue(array_key_exists('precision', $options), "Field options are 'precision' key");
        $this->assertEquals(5, $options['precision'], "Field options provided wrong value for 'precision' key");
        $this->assertTrue(array_key_exists('scale', $options), "Field options are 'scale' key");
        $this->assertEquals(6, $options['scale'], "Field options provided wrong value for 'scale' key");
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

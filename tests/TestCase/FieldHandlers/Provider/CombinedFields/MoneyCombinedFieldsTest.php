<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\CombinedFields;

use CsvMigrations\FieldHandlers\Config\MoneyConfig;
use CsvMigrations\FieldHandlers\Provider\CombinedFields\MoneyCombinedFields;
use PHPUnit_Framework_TestCase;

class MoneyCombinedFieldsTest extends PHPUnit_Framework_TestCase
{
    protected $field = 'salary';
    protected $provider;

    protected function setUp()
    {
        $config = new MoneyConfig($this->field);
        $this->provider = new MoneyCombinedFields($config);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->provider));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    public function testProvide()
    {
        $result = $this->provider->provide();
        $this->assertTrue(is_array($result), "Provder returned a non-array result");
        $this->assertFalse(empty($result), "Provider returned an empty array");

        $fields = array_keys($result);
        $this->assertEquals(2, count($fields), "Provider returned wrong number of fields");
        $this->assertTrue(in_array('amount', $fields), "Amount field is missing from result");
        $this->assertTrue(in_array('currency', $fields), "Currency field is missing from result");

        foreach ($result as $field => $options) {
            $this->assertTrue(is_array($options), "Options is not an array for field [$field]");
            $this->assertFalse(empty($options), "Options are empty for field [$field]");
            $this->assertTrue(array_key_exists('handler', $options), "Options for field [$field] are missing the 'handler' key");
            $this->assertTrue(is_string($options['handler']), "Options for field [$field] define a non-string 'handler' key");
            $this->assertTrue(class_exists($options['handler']), "Options for field [$field] define a non-existing class in 'handler' key");
        }
    }
}

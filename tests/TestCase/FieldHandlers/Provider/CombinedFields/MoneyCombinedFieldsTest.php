<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\CombinedFields;

use CsvMigrations\FieldHandlers\Config\MoneyConfig;
use CsvMigrations\FieldHandlers\Provider\CombinedFields\MoneyCombinedFields;
use PHPUnit\Framework\TestCase;

class MoneyCombinedFieldsTest extends TestCase
{
    protected $field = 'salary';
    protected $provider;

    protected function setUp() : void
    {
        $config = new MoneyConfig($this->field);
        $this->provider = new MoneyCombinedFields($config);
    }

    public function testInterface() : void
    {
        $implementedInterfaces = array_keys(class_implements($this->provider));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    public function testProvide() : void
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
            $this->assertTrue(array_key_exists('config', $options), "Options for field [$field] are missing the 'config' key");
            $this->assertTrue(is_string($options['config']), "Options for field [$field] define a non-string 'config' key");
            $this->assertTrue(class_exists($options['config']), "Options for field [$field] define a non-existing class in 'config' key");
        }
    }
}

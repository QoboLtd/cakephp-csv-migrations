<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\SearchOperators;

use CsvMigrations\FieldHandlers\Config\StringConfig;
use CsvMigrations\FieldHandlers\Provider\SearchOperators\StringSearchOperators;
use PHPUnit_Framework_TestCase;

class StringSearchOperatorsTest extends PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $config = new StringConfig('foobar');
        $this->provider = new StringSearchOperators($config);
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

        $operators = array_keys($result);
        $requiredOperators = ['contains', 'not_contains', 'starts_with', 'ends_with'];
        $this->assertEquals(count($requiredOperators), count($operators), "Operators count does not match required operators count");
        foreach ($requiredOperators as $operator) {
            $this->assertTrue(in_array($operator, $operators), "Required operator [$operator] is missing from result");
        }
    }
}

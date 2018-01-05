<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\SearchOperators;

use CsvMigrations\FieldHandlers\Config\StringConfig;
use CsvMigrations\FieldHandlers\Provider\SearchOperators\NullSearchOperators;
use PHPUnit_Framework_TestCase;

class NullSearchOperatorsTest extends PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $config = new StringConfig('foobar');
        $this->provider = new NullSearchOperators($config);
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
        $this->assertTrue(empty($result), "Provider returned a non-empty array");
    }
}

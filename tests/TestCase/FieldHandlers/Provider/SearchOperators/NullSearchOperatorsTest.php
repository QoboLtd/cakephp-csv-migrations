<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\SearchOperators;

use CsvMigrations\FieldHandlers\Config\StringConfig;
use CsvMigrations\FieldHandlers\Provider\SearchOperators\NullSearchOperators;
use PHPUnit\Framework\TestCase;

class NullSearchOperatorsTest extends TestCase
{
    protected $provider;

    protected function setUp(): void
    {
        $config = new StringConfig('foobar');
        $this->provider = new NullSearchOperators($config);
    }

    public function testInterface(): void
    {
        $implementedInterfaces = array_keys(class_implements($this->provider));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    public function testProvide(): void
    {
        $result = $this->provider->provide();
        $this->assertTrue(is_array($result), "Provder returned a non-array result");
        $this->assertTrue(empty($result), "Provider returned a non-empty array");
    }
}

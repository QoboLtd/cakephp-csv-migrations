<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\SearchOperators;

use CsvMigrations\FieldHandlers\Config\StringConfig;
use CsvMigrations\FieldHandlers\Provider\SearchOperators\BooleanSearchOperators;
use PHPUnit\Framework\TestCase;

class BooleanSearchOperatorsTest extends TestCase
{
    protected $provider;

    protected function setUp(): void
    {
        $config = new StringConfig('foobar');
        $this->provider = new BooleanSearchOperators($config);
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
        $this->assertFalse(empty($result), "Provider returned an empty array");

        $operators = array_keys($result);
        $requiredOperators = ['is', 'is_not'];
        $this->assertEquals(count($requiredOperators), count($operators), "Operators count does not match required operators count");
        foreach ($requiredOperators as $operator) {
            $this->assertTrue(in_array($operator, $operators), "Required operator [$operator] is missing from result");
        }
    }
}

<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\DbFieldType;

use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\Config\AggregatedConfig;
use CsvMigrations\FieldHandlers\Provider\DbFieldType\AggregatedDbFieldType;
use CsvMigrations\FieldHandlers\Provider\ProviderInterface;

class AggregatedDbFieldTypeTest extends TestCase
{
    private $provider;

    public function setUp(): void
    {
        $this->provider = new AggregatedDbFieldType(new AggregatedConfig('foobar'));
    }

    public function tearDown(): void
    {
        unset($this->provider);
    }

    public function testInterface(): void
    {
        $this->assertInstanceOf(ProviderInterface::class, $this->provider);
    }

    public function testProvide(): void
    {
        $this->assertSame('', $this->provider->provide());
    }
}

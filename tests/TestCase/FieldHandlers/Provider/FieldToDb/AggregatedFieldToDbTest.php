<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\FieldToDb;

use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\Config\AggregatedConfig;
use CsvMigrations\FieldHandlers\Provider\FieldToDb\AggregatedFieldToDb;
use CsvMigrations\FieldHandlers\Provider\ProviderInterface;

class AggregatedFieldToDbTest extends TestCase
{
    private $provider;

    public function setUp(): void
    {
        $this->provider = new AggregatedFieldToDb(new AggregatedConfig('foobar'));
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
        $this->assertSame([], $this->provider->provide());
    }
}

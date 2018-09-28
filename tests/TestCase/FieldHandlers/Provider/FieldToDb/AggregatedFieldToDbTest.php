<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\FieldToDb;

use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\Config\AggregatedConfig;
use CsvMigrations\FieldHandlers\Provider\FieldToDb\AggregatedFieldToDb;
use CsvMigrations\FieldHandlers\Provider\ProviderInterface;

class AggregatedFieldToDbTest extends TestCase
{
    public function setUp()
    {
        $this->provider = new AggregatedFieldToDb(new AggregatedConfig('foobar'));
    }

    public function tearDown()
    {
        unset($this->provider);
    }

    public function testInterface()
    {
        $this->assertInstanceOf(ProviderInterface::class, $this->provider);
    }

    public function testProvide()
    {
        $this->assertSame([], $this->provider->provide());
    }
}

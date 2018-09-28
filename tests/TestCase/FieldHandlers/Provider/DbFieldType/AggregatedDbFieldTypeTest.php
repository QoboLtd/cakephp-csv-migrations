<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\DbFieldType;

use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\Config\AggregatedConfig;
use CsvMigrations\FieldHandlers\Provider\DbFieldType\AggregatedDbFieldType;
use CsvMigrations\FieldHandlers\Provider\ProviderInterface;

class AggregatedDbFieldTypeTest extends TestCase
{
    public function setUp()
    {
        $this->provider = new AggregatedDbFieldType(new AggregatedConfig('foobar'));
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
        $this->assertSame('', $this->provider->provide());
    }
}

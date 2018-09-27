<?php
namespace CsvMigrations\Test\TestCase\Aggregator;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Aggregator\Configuration;
use CsvMigrations\Aggregator\MaxAggregator;
use RuntimeException;

class MaxAggregatorTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.foo'
    ];

    public function testValidateWithNonExistingField()
    {
        $this->expectException(RuntimeException::class);

        new MaxAggregator(new Configuration('Foo', 'non-existing-field'));
    }

    public function testApplyConditions()
    {
        $aggregator = new MaxAggregator(new Configuration('Foo', 'cost_amount'));

        $query = TableRegistry::get('Foo')->find('all');
        // clone query before modification
        $expected = clone $query;

        $aggregator->applyConditions($query);

        $this->assertNotEquals($expected, $query);
    }

    public function testGetResult()
    {
        $aggregator = new MaxAggregator(new Configuration('Foo', 'cost_amount'));

        $query = TableRegistry::get('Foo')->find('all');
        $query = $aggregator->applyConditions($query);

        $this->assertSame('2000.1', $aggregator->getResult($query->first()));
    }

    public function testGetConfig()
    {
        $aggregator = new MaxAggregator(new Configuration('Foo', 'cost_amount'));

        $this->assertInstanceOf(Configuration::class, $aggregator->getConfig());
    }
}

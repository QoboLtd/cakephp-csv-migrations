<?php
namespace CsvMigrations\Test\TestCase\Aggregator;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Aggregator\Configuration;
use CsvMigrations\Aggregator\LastAggregator;
use RuntimeException;

class LastAggregatorTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.foo'
    ];

    public function testValidateWithNonExistingField()
    {
        $this->expectException(RuntimeException::class);

        new LastAggregator(new Configuration('Foo', 'non-existing-field'));
    }

    public function testApplyConditions()
    {
        $aggregator = new LastAggregator(new Configuration('Foo', 'cost_amount'));

        $query = TableRegistry::get('Foo')->find('all');
        // clone query before modification
        $expected = clone $query;

        $aggregator->applyConditions($query);

        $this->assertNotEquals($expected, $query);
    }

    public function testGetResult()
    {
        $aggregator = new LastAggregator(new Configuration('Foo', 'created', 'status'));

        $query = TableRegistry::get('Foo')->find('all');
        $query = $aggregator->applyConditions($query);

        $this->assertSame('inactive', $aggregator->getResult($query->first()));
    }

    public function testGetConfig()
    {
        $aggregator = new LastAggregator(new Configuration('Foo', 'cost_amount'));

        $this->assertInstanceOf(Configuration::class, $aggregator->getConfig());
    }
}

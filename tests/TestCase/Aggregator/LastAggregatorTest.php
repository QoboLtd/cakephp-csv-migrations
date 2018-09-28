<?php
namespace CsvMigrations\Test\TestCase\Aggregator;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Aggregator\Configuration;
use CsvMigrations\Aggregator\LastAggregator;
use DateTime;
use RuntimeException;

class LastAggregatorTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.foo'
    ];

    public function setUp()
    {
        $this->table = TableRegistry::get('Foo');
    }

    public function tearDown()
    {
        unset($this->table);
    }

    public function testValidateWithNonExistingField()
    {
        $this->expectException(RuntimeException::class);

        new LastAggregator(new Configuration($this->table, 'non-existing-field'));
    }

    public function testApplyConditions()
    {
        $aggregator = new LastAggregator(new Configuration($this->table, 'cost_amount'));

        $query = $this->table->find('all');
        // clone query before modification
        $expected = clone $query;

        $aggregator->applyConditions($query);

        $this->assertNotEquals($expected, $query);
    }

    public function testGetResultWithString()
    {
        $configuration = new Configuration($this->table, 'created');
        $configuration->setDisplayField('status');
        $aggregator = new LastAggregator($configuration);

        $query = $this->table->find('all');
        $query = $aggregator->applyConditions($query);

        $this->assertSame('inactive', $aggregator->getResult($query->first()));
    }

    public function testGetResultWithDatetime()
    {
        $configuration = new Configuration($this->table, 'created');
        $aggregator = new LastAggregator($configuration);

        $query = $this->table->find('all');
        $query = $aggregator->applyConditions($query);

        $this->assertInstanceOf(DateTime::class, $aggregator->getResult($query->first()));
    }

    public function testGetConfig()
    {
        $aggregator = new LastAggregator(new Configuration($this->table, 'cost_amount'));

        $this->assertInstanceOf(Configuration::class, $aggregator->getConfig());
    }
}
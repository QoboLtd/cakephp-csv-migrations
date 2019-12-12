<?php

namespace CsvMigrations\Test\TestCase\Aggregator;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Aggregator\Configuration;
use CsvMigrations\Aggregator\LastAggregator;
use CsvMigrations\Aggregator\MaxAggregator;
use DateTime;
use RuntimeException;

class LastAggregatorTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.foo',
    ];

    private $table;

    public function setUp(): void
    {
        $this->table = TableRegistry::get('Foo');
    }

    public function tearDown(): void
    {
        unset($this->table);
    }

    public function testAliasMaxAggregator(): void
    {
        $result = new MaxAggregator(new Configuration($this->table, 'cost_amount'));

        $this->assertInstanceOf(LastAggregator::class, $result);
    }

    public function testValidateWithNonExistingField(): void
    {
        $this->expectException(RuntimeException::class);

        new LastAggregator(new Configuration($this->table, 'non-existing-field'));
    }

    public function testApplyConditions(): void
    {
        $aggregator = new LastAggregator(new Configuration($this->table, 'cost_amount'));

        $query = $this->table->find('all');
        // clone query before modification
        $expected = clone $query;

        $aggregator->applyConditions($query);

        $this->assertNotEquals($expected, $query);
    }

    public function testGetResultWithDecimal(): void
    {
        $configuration = new Configuration($this->table, 'cost_amount');
        $aggregator = new LastAggregator($configuration);

        $query = $this->table->find('all');
        $query = $aggregator->applyConditions($query);

        $this->assertSame(2000.10, $aggregator->getResult($query->first()));
    }

    public function testGetResultWithString(): void
    {
        $configuration = new Configuration($this->table, 'created');
        $configuration->setDisplayField('status');
        $aggregator = new LastAggregator($configuration);

        $query = $this->table->find('all');
        $query = $aggregator->applyConditions($query);

        $this->assertSame('inactive', $aggregator->getResult($query->first()));
    }

    public function testGetResultWithDatetime(): void
    {
        $configuration = new Configuration($this->table, 'created');
        $aggregator = new LastAggregator($configuration);

        $query = $this->table->find('all');
        $query = $aggregator->applyConditions($query);
        $result = $aggregator->getResult($query->first());

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertSame('2018-09-26 10:39:23', $result->i18nFormat('yyyy-MM-dd HH:mm:ss'));
    }

    public function testGetConfig(): void
    {
        $aggregator = new LastAggregator(new Configuration($this->table, 'cost_amount'));

        $this->assertInstanceOf(Configuration::class, $aggregator->getConfig());
    }
}

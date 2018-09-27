<?php
namespace CsvMigrations\Test\TestCase\Aggregator;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Aggregator\AggregateResult;
use CsvMigrations\Aggregator\AverageAggregator;
use CsvMigrations\Aggregator\Configuration;
use CsvMigrations\Aggregator\LastAggregator;
use CsvMigrations\Aggregator\MaxAggregator;
use CsvMigrations\Aggregator\SumAggregator;
use RuntimeException;

class AggregateTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.articles',
        'plugin.CsvMigrations.foo',
        'plugin.CsvMigrations.leads'
    ];

    public function setUp()
    {
        $this->articlesTable = TableRegistry::get('Articles');
        $this->fooTable = TableRegistry::get('Foo');
        $this->leadsTable = TableRegistry::get('Leads');
    }

    public function tearDown()
    {
        unset($this->leadsTable);
        unset($this->fooTable);
        unset($this->articlesTable);
    }

    /**
     * @dataProvider aggregatorResultProvider
     */
    public function testGetResult($aggregatorClass, $expected)
    {
        $aggregator = new $aggregatorClass(new Configuration($this->fooTable, 'cost_amount'));

        $this->assertSame($expected, AggregateResult::get($aggregator));
    }

    public function aggregatorResultProvider()
    {
        return [
            [SumAggregator::class, '3300.3'],
            [AverageAggregator::class, '1100.1'],
            [MaxAggregator::class, '2000.1'],
            [LastAggregator::class, '2000.1']
        ];
    }

    /**
     * @dataProvider aggregatorResultProviderWithJoin
     */
    public function testGetResultWithJoin($aggregatorClass, $expected)
    {
        $entity = $this->leadsTable->get('00000000-0000-0000-0000-000000000001');
        $configuration = new Configuration($this->fooTable, 'cost_amount');
        $configuration->setJoinData($this->leadsTable, $entity);
        $aggregator = new $aggregatorClass($configuration);

        $this->assertSame($expected, AggregateResult::get($aggregator));
    }

    public function aggregatorResultProviderWithJoin()
    {
        return [
            [SumAggregator::class, '3000.2'],
            [AverageAggregator::class, '1500.1'],
            [MaxAggregator::class, '2000.1'],
            [LastAggregator::class, '2000.1']
        ];
    }

    public function testGetResultWithEmptyReturnedValue()
    {
        // this lead has no foo records associated with it
        $entity = $this->leadsTable->get('00000000-0000-0000-0000-000000000003');
        $configuration = new Configuration($this->fooTable, 'cost_amount');
        $configuration->setDisplayField('status')
            ->setJoinData($this->leadsTable, $entity);
        $aggregator = new LastAggregator($configuration);

        $this->assertSame('', AggregateResult::get($aggregator));
    }

    public function testGetResultWithNonAssociatedTable()
    {
        $this->expectException(RuntimeException::class);

        $entity = $this->articlesTable->get('00000000-0000-0000-0000-000000000001');
        $configuration = new Configuration($this->fooTable, 'cost_amount');
        $configuration->setJoinData($this->articlesTable, $entity);
        $aggregator = new SumAggregator($configuration);

        AggregateResult::get($aggregator);
    }

    public function testGetResultWithUnsupportedAssociation()
    {
        $this->expectException(RuntimeException::class);

        $entity = $this->fooTable->get('00000000-0000-0000-0000-000000000001');
        $configuration = new Configuration($this->leadsTable, 'name');
        $configuration->setJoinData($this->fooTable, $entity);
        $aggregator = new LastAggregator($configuration);

        AggregateResult::get($aggregator);
    }
}

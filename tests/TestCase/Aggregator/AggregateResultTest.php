<?php

namespace CsvMigrations\Test\TestCase\Aggregator;

use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Aggregator\AggregateResult;
use CsvMigrations\Aggregator\AverageAggregator;
use CsvMigrations\Aggregator\Configuration;
use CsvMigrations\Aggregator\LastAggregator;
use CsvMigrations\Aggregator\MaxAggregator;
use CsvMigrations\Aggregator\SumAggregator;
use RuntimeException;

class AggregateResultTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.articles',
        'plugin.CsvMigrations.foo',
        'plugin.CsvMigrations.leads',
    ];

    private $articlesTable;
    private $fooTable;
    private $leadsTable;

    public function setUp(): void
    {
        $this->articlesTable = TableRegistry::get('Articles');
        $this->fooTable = TableRegistry::get('Foo');
        $this->leadsTable = TableRegistry::get('Leads');
    }

    public function tearDown(): void
    {
        unset($this->leadsTable);
        unset($this->fooTable);
        unset($this->articlesTable);
    }

    /**
     * @param  mixed $expected
     * @dataProvider aggregatorResultProvider
     */
    public function testGetResult(string $aggregatorClass, string $field, $expected): void
    {
        $aggregator = new $aggregatorClass(new Configuration($this->fooTable, $field));

        $this->assertEquals($expected, AggregateResult::get($aggregator));
    }

    /**
     * @return mixed[]
     */
    public function aggregatorResultProvider(): array
    {
        return [
            [SumAggregator::class, 'cost_amount', 3300.3],
            [AverageAggregator::class, 'cost_amount', 1100.1],
            [MaxAggregator::class, 'cost_amount', 2000.1],
            [LastAggregator::class, 'cost_amount', 2000.1],
            [LastAggregator::class, 'status', 'inactive'],
            [LastAggregator::class, 'created', new Time('2018-09-26 10:39:23')],
            [LastAggregator::class, 'lead', '00000000-0000-0000-0000-000000000002'],
        ];
    }

    /**
     * @dataProvider aggregatorResultProviderWithJoin
     */
    public function testGetResultWithJoin(string $aggregatorClass, float $expected): void
    {
        $entity = $this->leadsTable->get('00000000-0000-0000-0000-000000000001');
        $configuration = new Configuration($this->fooTable, 'cost_amount');
        $configuration->setJoinData($this->leadsTable, $entity);
        $aggregator = new $aggregatorClass($configuration);

        $this->assertSame($expected, AggregateResult::get($aggregator));
    }

    /**
     * @return mixed[]
     */
    public function aggregatorResultProviderWithJoin(): array
    {
        return [
            [SumAggregator::class, 3000.2],
            [AverageAggregator::class, 1500.1],
            [MaxAggregator::class, 2000.1],
            [LastAggregator::class, 2000.1],
        ];
    }

    public function testGetResultWithEmptyReturnedValue(): void
    {
        // this lead has no foo records associated with it
        $entity = $this->leadsTable->get('00000000-0000-0000-0000-000000000003');
        $configuration = new Configuration($this->fooTable, 'cost_amount');
        $configuration->setDisplayField('status')
            ->setJoinData($this->leadsTable, $entity);
        $aggregator = new LastAggregator($configuration);

        $this->assertSame('', AggregateResult::get($aggregator));
    }

    public function testGetResultWithNonAssociatedTable(): void
    {
        $this->expectException(RuntimeException::class);

        $entity = $this->articlesTable->get('00000000-0000-0000-0000-000000000001');
        $configuration = new Configuration($this->fooTable, 'cost_amount');
        $configuration->setJoinData($this->articlesTable, $entity);
        $aggregator = new SumAggregator($configuration);

        AggregateResult::get($aggregator);
    }

    public function testGetResultWithUnsupportedAssociation(): void
    {
        $this->expectException(RuntimeException::class);

        $entity = $this->fooTable->get('00000000-0000-0000-0000-000000000001');
        $configuration = new Configuration($this->leadsTable, 'name');
        $configuration->setJoinData($this->fooTable, $entity);
        $aggregator = new LastAggregator($configuration);

        AggregateResult::get($aggregator);
    }
}

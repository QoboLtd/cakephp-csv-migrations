<?php

namespace CsvMigrations\Test\TestCase\Aggregator;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Aggregator\AverageAggregator;
use CsvMigrations\Aggregator\Configuration;
use RuntimeException;

class AverageAggregatorTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.foo',
    ];

    private $table;

    public function setUp(): void
    {
        $this->table = TableRegistry::getTableLocator()->get('Foo');
    }

    public function tearDown(): void
    {
        unset($this->table);
    }

    public function testValidateWithNonExistingField(): void
    {
        $this->expectException(RuntimeException::class);

        new AverageAggregator(new Configuration($this->table, 'non-existing-field'));
    }

    /**
     * @dataProvider invalidFieldTypesProvider
     */
    public function testValidateWithInvalidFieldType(string $field): void
    {
        $this->expectException(RuntimeException::class);

        new AverageAggregator(new Configuration($this->table, $field));
    }

    public function testApplyConditions(): void
    {
        $aggregator = new AverageAggregator(new Configuration($this->table, 'cost_amount'));

        $query = $this->table->find('all');
        // clone query before modification
        $expected = clone $query;

        $aggregator->applyConditions($query);

        $this->assertNotEquals($expected, $query);
    }

    public function testGetResult(): void
    {
        $aggregator = new AverageAggregator(new Configuration($this->table, 'cost_amount'));

        $query = $this->table->find('all');
        $query = $aggregator->applyConditions($query);

        $this->assertSame(1100.1, $aggregator->getResult($query->first()));
    }

    public function testGetConfig(): void
    {
        $aggregator = new AverageAggregator(new Configuration($this->table, 'cost_amount'));

        $this->assertInstanceOf(Configuration::class, $aggregator->getConfig());
    }

    /**
     * @return mixed[]
     */
    public function invalidFieldTypesProvider(): array
    {
        return [
            ['cost_currency'],
            ['description'],
            ['start_time'],
            ['created'],
            ['birthdate'],
            ['created_by'],
        ];
    }
}

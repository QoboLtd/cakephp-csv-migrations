<?php
namespace CsvMigrations\Test\TestCase\Aggregator;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Aggregator\Configuration;
use CsvMigrations\Aggregator\SumAggregator;
use RuntimeException;

class SumAggregatorTest extends TestCase
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

        new SumAggregator(new Configuration($this->table, 'non-existing-field'));
    }

    /**
     * @dataProvider invalidFieldTypesProvider
     */
    public function testValidateWithInvalidFieldType($field)
    {
        $this->expectException(RuntimeException::class);

        new SumAggregator(new Configuration($this->table, $field));
    }

    public function testApplyConditions()
    {
        $aggregator = new SumAggregator(new Configuration($this->table, 'cost_amount'));

        $query = $this->table->find('all');
        // clone query before modification
        $expected = clone $query;

        $aggregator->applyConditions($query);

        $this->assertNotEquals($expected, $query);
    }

    public function testGetResult()
    {
        $aggregator = new SumAggregator(new Configuration($this->table, 'cost_amount'));

        $query = $this->table->find('all');
        $query = $aggregator->applyConditions($query);

        $this->assertSame('3300.3', $aggregator->getResult($query->first()));
    }

    public function testGetConfig()
    {
        $aggregator = new SumAggregator(new Configuration($this->table, 'cost_amount'));

        $this->assertInstanceOf(Configuration::class, $aggregator->getConfig());
    }

    public function invalidFieldTypesProvider()
    {
        return [
            ['cost_currency'],
            ['description'],
            ['start_time'],
            ['created'],
            ['birthdate'],
            ['created_by']
        ];
    }
}

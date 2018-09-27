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

    public function testValidateWithNonExistingField()
    {
        $this->expectException(RuntimeException::class);

        new SumAggregator(new Configuration('Foo', 'non-existing-field'));
    }

    /**
     * @dataProvider invalidFieldTypesProvider
     */
    public function testValidateWithInvalidFieldType($field)
    {
        $this->expectException(RuntimeException::class);

        new SumAggregator(new Configuration('Foo', $field));
    }

    public function testApplyConditions()
    {
        $aggregator = new SumAggregator(new Configuration('Foo', 'cost_amount'));

        $query = TableRegistry::get('Foo')->find('all');
        // clone query before modification
        $expected = clone $query;

        $aggregator->applyConditions($query);

        $this->assertNotEquals($expected, $query);
    }

    public function testGetResult()
    {
        $aggregator = new SumAggregator(new Configuration('Foo', 'cost_amount'));

        $query = TableRegistry::get('Foo')->find('all');
        $query = $aggregator->applyConditions($query);

        $this->assertSame('3300.3', $aggregator->getResult($query->first()));
    }

    public function testGetConfig()
    {
        $aggregator = new SumAggregator(new Configuration('Foo', 'cost_amount'));

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

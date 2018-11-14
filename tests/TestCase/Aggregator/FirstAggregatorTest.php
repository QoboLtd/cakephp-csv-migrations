<?php
namespace CsvMigrations\Test\TestCase\Aggregator;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Aggregator\Configuration;
use CsvMigrations\Aggregator\FirstAggregator;
use CsvMigrations\Aggregator\MinAggregator;
use DateTime;
use RuntimeException;

class FirstAggregatorTest extends TestCase
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

    public function testAliasMinAggregator()
    {
        $result = new MinAggregator(new Configuration($this->table, 'cost_amount'));

        $this->assertInstanceOf(FirstAggregator::class, $result);
    }

    public function testValidateWithNonExistingField()
    {
        $this->expectException(RuntimeException::class);

        new FirstAggregator(new Configuration($this->table, 'non-existing-field'));
    }

    public function testApplyConditions()
    {
        $aggregator = new FirstAggregator(new Configuration($this->table, 'cost_amount'));

        $query = $this->table->find('all');
        // clone query before modification
        $expected = clone $query;

        $aggregator->applyConditions($query);

        $this->assertNotEquals($expected, $query);
    }

    public function testGetResultWithDecimal()
    {
        $configuration = new Configuration($this->table, 'cost_amount');
        $aggregator = new FirstAggregator($configuration);

        $query = $this->table->find('all');
        $query = $aggregator->applyConditions($query);

        $this->assertSame(300.10, $aggregator->getResult($query->first()));
    }

    public function testGetResultWithString()
    {
        $configuration = new Configuration($this->table, 'created');
        $configuration->setDisplayField('status');
        $aggregator = new FirstAggregator($configuration);

        $query = $this->table->find('all');
        $query = $aggregator->applyConditions($query);

        $this->assertSame('active', $aggregator->getResult($query->first()));
    }

    public function testGetResultWithDatetime()
    {
        $configuration = new Configuration($this->table, 'created');
        $aggregator = new FirstAggregator($configuration);

        $query = $this->table->find('all');
        $query = $aggregator->applyConditions($query);
        $result = $aggregator->getResult($query->first());

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertSame('2016-07-01 10:39:23', $result->i18nFormat('yyyy-MM-dd HH:mm:ss'));
    }

    public function testGetConfig()
    {
        $aggregator = new FirstAggregator(new Configuration($this->table, 'cost_amount'));

        $this->assertInstanceOf(Configuration::class, $aggregator->getConfig());
    }
}

<?php

namespace CsvMigrations\Test\TestCase\Aggregator;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Aggregator\Configuration;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class ConfigurationTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.articles',
        'plugin.CsvMigrations.foo',
    ];

    public function testConstructor(): void
    {
        $result = new Configuration(TableRegistry::get('tableName'), 'field');

        $this->assertInstanceOf(Configuration::class, $result);
    }

    public function testJoinMode(): void
    {
        $table = TableRegistry::get('Foo');
        $entity = $table->find()
            ->enableHydration(true)
            ->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);
        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');

        $configuration->setJoinData($table, $entity);
        $this->assertTrue($configuration->joinMode());

        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');
        $this->assertFalse($configuration->joinMode());
    }

    public function testGetTable(): void
    {
        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');

        $this->assertInstanceOf(RepositoryInterface::class, $configuration->getTable());
    }

    public function testGetField(): void
    {
        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');

        $this->assertEquals('field', $configuration->getField());
    }

    public function testSetGetDisplayField(): void
    {
        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');
        $configuration->setDisplayField('displayField');

        $this->assertEquals('displayField', $configuration->getDisplayField());
    }

    public function testGetJoinTable(): void
    {
        $table = TableRegistry::get('Foo');
        $entity = $table->find()
            ->enableHydration(true)
            ->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);
        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');
        $configuration->setJoinData($table, $entity);

        $this->assertSame($table, $configuration->getJoinTable());
    }

    public function testGetEntity(): void
    {
        $table = TableRegistry::get('Foo');
        $entity = $table->find()
            ->enableHydration(true)
            ->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);
        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');
        $configuration->setJoinData($table, $entity);

        $this->assertSame($entity, $configuration->getEntity());
    }

    public function testSetJoinDataWithoutInvalidEntity(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $table = TableRegistry::get('Foo');
        $entity = TableRegistry::get('Articles')
            ->find()
            ->enableHydration(true)
            ->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);

        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');

        // using entity from another table, instead of the configured join table (Foo)
        $configuration->setJoinData($table, $entity);
    }
}

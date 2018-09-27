<?php
namespace CsvMigrations\Test\TestCase\Aggregator;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Aggregator\Configuration;
use InvalidArgumentException;

class ConfigurationTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.articles',
        'plugin.CsvMigrations.foo'
    ];

    public function testConstructor()
    {
        new Configuration('tableName', 'field');
    }

    public function testConstructorWithAllAgruments()
    {
        $entity = TableRegistry::get('Foo')->find()->first();

        new Configuration('tableName', 'field', 'displayField', 'Foo', $entity);
    }

    public function testConstructorWithInvalidConfig()
    {
        $this->expectException(InvalidArgumentException::class);

        // invalid first parameter, string expected
        new Configuration(['tableName'], 'field');
    }

    public function testConstructorWithoutRequiredEntity()
    {
        $this->expectException(InvalidArgumentException::class);

        // since join table is provided, entity is required
        new Configuration('tableName', 'field', 'displayField', 'joinTable');
    }

    public function testConstructorWittInvalidEntity()
    {
        $this->expectException(InvalidArgumentException::class);

        // using entity from another table, instead of the configured join table (Foo)
        $entity = TableRegistry::get('Articles')->find()->first();

        new Configuration('tableName', 'field', 'displayField', 'Foo', $entity);
    }

    public function testJoinMode()
    {
        $entity = TableRegistry::get('Foo')->find()->first();
        $configuration = new Configuration('tableName', 'field', '', 'Foo', $entity);
        $this->assertTrue($configuration->joinMode());

        $configuration = new Configuration('tableName', 'field');
        $this->assertFalse($configuration->joinMode());
    }

    public function testGetTable()
    {
        $configuration = new Configuration('tableName', 'field');

        $this->assertInstanceOf(RepositoryInterface::class, $configuration->getTable());
    }

    public function testGetField()
    {
        $configuration = new Configuration('tableName', 'field');

        $this->assertEquals('field', $configuration->getField());
    }

    public function testGetDisplayField()
    {
        $configuration = new Configuration('tableName', 'field', 'displayField');

        $this->assertEquals('displayField', $configuration->getDisplayField());
    }

    public function testGetJoinTable()
    {
        $entity = TableRegistry::get('Foo')->find()->first();
        $configuration = new Configuration('tableName', 'field', 'displayField', 'Foo', $entity);

        $this->assertInstanceOf(RepositoryInterface::class, $configuration->getJoinTable());
    }

    public function testGetEntity()
    {
        $entity = TableRegistry::get('Foo')->find()->first();
        $configuration = new Configuration('tableName', 'field', 'displayField', 'Foo', $entity);

        $this->assertInstanceOf(EntityInterface::class, $configuration->getEntity());
        $this->assertSame($entity, $configuration->getEntity());
    }
}

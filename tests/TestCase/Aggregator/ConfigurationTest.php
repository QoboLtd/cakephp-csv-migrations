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
        $result = new Configuration(TableRegistry::get('tableName'), 'field');

        $this->assertInstanceOf(Configuration::class, $result);
    }

    public function testConstructorWithInvalidConfig()
    {
        $this->expectException(InvalidArgumentException::class);

        // invalid second parameter, string expected
        new Configuration(TableRegistry::get('tableName'), ['field']);
    }

    public function testJoinMode()
    {
        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');
        $configuration->setJoinData(
            TableRegistry::get('Foo'),
            TableRegistry::get('Foo')->find()->first()
        );
        $this->assertTrue($configuration->joinMode());

        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');
        $this->assertFalse($configuration->joinMode());
    }

    public function testGetTable()
    {
        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');

        $this->assertInstanceOf(RepositoryInterface::class, $configuration->getTable());
    }

    public function testGetField()
    {
        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');

        $this->assertEquals('field', $configuration->getField());
    }

    public function testSetGetDisplayField()
    {
        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');
        $configuration->setDisplayField('displayField');

        $this->assertEquals('displayField', $configuration->getDisplayField());
    }

    public function testSetDisplayFieldWithWrongParameter()
    {
        $this->expectException(InvalidArgumentException::class);

        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');
        $configuration->setDisplayField(['wrong_parameter']);
    }

    public function testGetJoinTable()
    {
        $table = TableRegistry::get('Foo');
        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');
        $configuration->setJoinData($table, $table->find()->first());

        $this->assertSame($table, $configuration->getJoinTable());
    }

    public function testGetEntity()
    {
        $table = TableRegistry::get('Foo');
        $entity = $table->find()->first();
        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');
        $configuration->setJoinData($table, $entity);

        $this->assertSame($entity, $configuration->getEntity());
    }

    public function testSetJoinDataWithoutInvalidEntity()
    {
        $this->expectException(InvalidArgumentException::class);

        $configuration = new Configuration(TableRegistry::get('tableName'), 'field');
        $configuration->setJoinData(
            TableRegistry::get('Foo'),
            // using entity from another table, instead of the configured join table (Foo)
            TableRegistry::get('Articles')->find()->first()
        );
    }
}

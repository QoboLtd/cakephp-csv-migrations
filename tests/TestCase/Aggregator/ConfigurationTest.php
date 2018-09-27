<?php
namespace CsvMigrations\Test\TestCase\Aggregator;

use Cake\Datasource\RepositoryInterface;
use Cake\TestSuite\TestCase;
use CsvMigrations\Aggregator\Configuration;
use InvalidArgumentException;

class ConfigurationTest extends TestCase
{
    public function testConstructorWithInvalidConfig()
    {
        $this->expectException(InvalidArgumentException::class);

        // invalid first parameter, string expected
        new Configuration(['tableName'], 'field');
    }

    public function testGetTable()
    {
        $config = new Configuration('tableName', 'field');

        $this->assertInstanceOf(RepositoryInterface::class, $config->getTable());
    }

    public function testGetField()
    {
        $config = new Configuration('tableName', 'field');

        $this->assertEquals('field', $config->getField());
    }

    public function testGetDisplayField()
    {
        $config = new Configuration('tableName', 'field', 'displayField');

        $this->assertEquals('displayField', $config->getDisplayField());
    }

    public function testGetJoinTable()
    {
        $config = new Configuration('tableName', 'field', 'displayField', 'joinTable');

        $this->assertInstanceOf(RepositoryInterface::class, $config->getJoinTable());
    }
}

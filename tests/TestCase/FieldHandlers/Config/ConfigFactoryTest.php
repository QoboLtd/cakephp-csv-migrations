<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Config;

use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use CsvMigrations\FieldHandlers\Config\ConfigInterface;
use PHPUnit_Framework_TestCase;

class ConfigFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testGetByType()
    {
        $result = ConfigFactory::getByType('string', 'foo');
        $this->assertTrue(is_object($result), "ConfigFactory returned a non-object result");
        $this->assertTrue($result instanceof ConfigInterface, "ConfigFactory returned invalid interface instance");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetByTypeException()
    {
        $result = ConfigFactory::getByType('unsupported_type', 'foo');
    }
}

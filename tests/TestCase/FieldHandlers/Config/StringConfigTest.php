<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Config;

use CsvMigrations\FieldHandlers\Config\Config;
use CsvMigrations\FieldHandlers\Config\ConfigInterface;
use CsvMigrations\FieldHandlers\Config\StringConfig;
use PHPUnit_Framework_TestCase;

class StringConfigTest extends PHPUnit_Framework_TestCase
{
    public function invalidProvidersProvider()
    {
        return [
            // empty config
            [[]],
            // invalid type for searchOperators
            [['searchOperators' => true]],
            // non-existing class
            [['searchOperators' => '\\Foo\\Bar\\No\\Exist']],
            // class missing SearchOperatorsInterface
            [['searchOperators' => '\\stdClass' ]],
        ];
    }

    public function validProvidersProvider()
    {
        return [
            [['searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators']],
        ];
    }

    /**
     * @dataProvider validProvidersProvider
     * @expectedException \InvalidArgumentException
     */
    public function testSetProvidersException($providers)
    {
        $configInstance = new StringConfig('foo');
        $configInstance->setProviders($providers);
    }

    /**
     * @dataProvider invalidProvidersProvider
     * @expectedException \InvalidArgumentException
     */
    public function testValidateProvidersException($providers)
    {
        $configInstance = new StringConfig('foo');
        $configInstance->validateProviders($providers);
    }

    public function testConstruct()
    {
        $configInstance = new StringConfig('foo');
        $this->assertTrue(is_object($configInstance), "Failed to instantiate StringConfig object");
        $this->assertTrue($configInstance instanceof ConfigInterface, "StringConfig instance does not implement ConfigInterface");
    }

    public function testGetProviders()
    {
        $configInstance = new StringConfig('foo');
        $actualProviders = $configInstance->getProviders();
        $this->assertTrue(is_array($actualProviders), "StringConfig did not return an array of providers");
        $this->assertFalse(empty($actualProviders), "StringConfig return an empty list of providers");
    }
}

<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\Config;

use CsvMigrations\FieldHandlers\Provider\Config\Config;
use CsvMigrations\FieldHandlers\Provider\Config\ConfigInterface;
use CsvMigrations\FieldHandlers\Provider\Config\StringConfig;
use PHPUnit_Framework_TestCase;

class StringConfigTest extends PHPUnit_Framework_TestCase
{
    public function invalidConfigProvider()
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

    public function validConfigProvider()
    {
        return [
            [['searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators']],
        ];
    }

    /**
     * @dataProvider validConfigProvider
     * @expectedException \InvalidArgumentException
     */
    public function testSetConfigException($config)
    {
        $configInstance = new StringConfig();
        $configInstance->setConfig($config);
    }

    /**
     * @dataProvider invalidConfigProvider
     * @expectedException \InvalidArgumentException
     */
    public function testValidateConfigException($config)
    {
        $configInstance = new StringConfig();
        $configInstance->validateConfig($config);
    }

    public function testConstruct()
    {
        $configInstance = new StringConfig();
        $this->assertTrue(is_object($configInstance), "Failed to instantiate StringConfig object");
        $this->assertTrue($configInstance instanceof ConfigInterface, "StringConfig instance does not implement ConfigInterface");
    }

    public function testGetConfig()
    {
        $configInstance = new StringConfig();
        $actualConfig = $configInstance->getConfig();
        $this->assertTrue(is_array($actualConfig), "StringConfig did not return an array config");
        $this->assertFalse(empty($actualConfig), "StringConfig return an empty config");
    }
}

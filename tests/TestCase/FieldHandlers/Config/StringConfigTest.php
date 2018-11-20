<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Config;

use CsvMigrations\FieldHandlers\Config\Config;
use CsvMigrations\FieldHandlers\Config\ConfigInterface;
use CsvMigrations\FieldHandlers\Config\StringConfig;
use PHPUnit\Framework\TestCase;

class StringConfigTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function invalidProvidersProvider() : array
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

    /**
     * @return mixed[]
     */
    public function validProvidersProvider() : array
    {
        return [
            [['searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators']],
        ];
    }

    /**
     * @param mixed[] $providers
     * @dataProvider validProvidersProvider
     * @expectedException \InvalidArgumentException
     */
    public function testSetProvidersException(array $providers) : void
    {
        $configInstance = new StringConfig('foo');
        $configInstance->setProviders($providers);
    }

    /**
     * @param mixed[] $providers
     * @dataProvider invalidProvidersProvider
     * @expectedException \InvalidArgumentException
     */
    public function testValidateProvidersException(array $providers) : void
    {
        $configInstance = new StringConfig('foo');
        $configInstance->validateProviders($providers);
    }

    public function testConstruct() : void
    {
        $configInstance = new StringConfig('foo');
        $this->assertTrue(is_object($configInstance), "Failed to instantiate StringConfig object");
        $this->assertTrue($configInstance instanceof ConfigInterface, "StringConfig instance does not implement ConfigInterface");
    }

    public function testGetProviders() : void
    {
        $configInstance = new StringConfig('foo');
        $actualProviders = $configInstance->getProviders();
        $this->assertTrue(is_array($actualProviders), "StringConfig did not return an array of providers");
        $this->assertFalse(empty($actualProviders), "StringConfig return an empty list of providers");
    }
}

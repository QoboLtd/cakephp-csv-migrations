<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\Config;

use CsvMigrations\FieldHandlers\Provider\Config\Config;
use CsvMigrations\FieldHandlers\Provider\Config\ConfigInterface;
use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
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
            // class missing interface searchOperators
            [['searchOperators' => '\\stdClass' ]],
            // all but FieldValue type is OK
            [
                [
                    'fieldValue' => true,
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Renderer\\Value\\StringRenderer',
                    'nameRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
                ],
            ],
            // all but FieldValue interface is OK
            [
                [
                    'fieldValue' => '\\stdClass',
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Renderer\\Value\\StringRenderer',
                    'nameRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
                ],
            ],
            // all but FieldValue class existing is OK
            [
                [
                    'fieldValue' => '\\Foo\\Bar\\No\\Exist',
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Renderer\\Value\\StringRenderer',
                    'nameRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
                ],
            ],

        ];
    }

    public function validConfigProvider()
    {
        return [
            [
                [
                    'fieldValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldValue\\MixedFieldValue',
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Renderer\\Value\\StringRenderer',
                    'nameRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidConfigProvider
     * @expectedException \InvalidArgumentException
     */
    public function testSetConfigException($config)
    {
        $configInstance = new Config('foo');
        $configInstance->setConfig($config);
    }

    /**
     * @dataProvider invalidConfigProvider
     * @expectedException \InvalidArgumentException
     */
    public function testValidateConfigException($config)
    {
        $configInstance = new Config('foo');
        $configInstance->validateConfig($config);
    }

    public function testConstruct()
    {
        $configInstance = new Config('foo');
        $this->assertTrue(is_object($configInstance), "Failed to instantiate Config object");
        $this->assertTrue($configInstance instanceof ConfigInterface, "Config instance does not implement ConfigInterface");
    }

    /**
     * @dataProvider validConfigProvider
     */
    public function testGetConfig($config)
    {
        $configInstance = new Config('foo');
        $configInstance->setConfig($config);
        $actualConfig = $configInstance->getConfig();
        $this->assertEquals($config, $actualConfig, "Config did not return provided config");
    }
}

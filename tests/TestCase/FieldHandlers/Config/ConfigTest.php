<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Config;

use Cake\ORM\Table;
use CsvMigrations\FieldHandlers\Config\Config;
use CsvMigrations\FieldHandlers\Config\ConfigInterface;
use PHPUnit_Framework_TestCase;
use stdClass;

class ConfigTest extends PHPUnit_Framework_TestCase
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
            // class missing interface searchOperators
            [['searchOperators' => '\\stdClass' ]],
            // all but FieldValue type is OK
            [
                [
                    'fieldValue' => true,
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\StringRenderer',
                    'nameRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
                ],
            ],
            // all but FieldValue interface is OK
            [
                [
                    'fieldValue' => '\\stdClass',
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\StringRenderer',
                    'nameRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
                ],
            ],
            // all but FieldValue class existing is OK
            [
                [
                    'fieldValue' => '\\Foo\\Bar\\No\\Exist',
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\StringRenderer',
                    'nameRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
                ],
            ],

        ];
    }

    public function validProvidersProvider()
    {
        return [
            [
                [
                    'combinedFields' => '\\CsvMigrations\\FieldHandlers\\Provider\\CombinedFields\\NullCombinedFields',
                    'fieldValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldValue\\MixedFieldValue',
                    'fieldToDb' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldToDb\\StringFieldToDb',
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'searchOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOptions\\StringSearchOptions',
                    'selectOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SelectOptions\\NullSelectOptions',
                    'inputRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderInput\\StringRenderer',
                    'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\StringRenderer',
                    'nameRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidProvidersProvider
     * @expectedException \InvalidArgumentException
     */
    public function testSetProvidersException($providers)
    {
        $configInstance = new Config('foo');
        $configInstance->setProviders($providers);
    }

    /**
     * @dataProvider invalidProvidersProvider
     * @expectedException \InvalidArgumentException
     */
    public function testValidateProvidersException($providers)
    {
        $configInstance = new Config('foo');
        $configInstance->validateProviders($providers);
    }

    public function testConstruct()
    {
        $configInstance = new Config('foo');
        $this->assertTrue(is_object($configInstance), "Failed to instantiate Config object");
        $this->assertTrue($configInstance instanceof ConfigInterface, "Config instance does not implement ConfigInterface");
    }

    /**
     * @dataProvider validProvidersProvider
     */
    public function testGetProviders($providers)
    {
        $configInstance = new Config('foo');
        $configInstance->setProviders($providers);
        $actualProviders = $configInstance->getProviders();
        $this->assertEquals($providers, $actualProviders, "Providers did not return provided list");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetFieldExceptionNotString()
    {
        $configInstance = new Config([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetFieldExceptionEmptyString()
    {
        $configInstance = new Config('   ');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetTableExceptionNotTable()
    {
        $configInstance = new Config('field', new stdClass());
    }

    public function testGettable()
    {
        $configInstance = new Config('field');
        $result = $configInstance->getTable();
        $this->assertTrue($result instanceof Table, "Config table returned a non-valid instance");
    }
}

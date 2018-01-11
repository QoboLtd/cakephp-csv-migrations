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
            // all good, but combinedFields is of wrong type
            [
                [
                    'combinedFields' => true,
                    'fieldValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldValue\\MixedFieldValue',
                    'fieldToDb' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldToDb\\StringFieldToDb',
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'searchOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOptions\\StringSearchOptions',
                    'selectOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SelectOptions\\NullSelectOptions',
                    'renderInput' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderInput\\StringRenderer',
                    'renderValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\StringRenderer',
                    'renderName' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
                ],
            ],
            // all good, but combinedFields class does not implement ProviderInterface
            [
                [
                    'combinedFields' => '\\StdClass',
                    'fieldValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldValue\\MixedFieldValue',
                    'fieldToDb' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldToDb\\StringFieldToDb',
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'searchOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOptions\\StringSearchOptions',
                    'selectOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SelectOptions\\NullSelectOptions',
                    'renderInput' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderInput\\StringRenderer',
                    'renderValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\StringRenderer',
                    'renderName' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
                ],
            ],
            // all good, but combinedFields class does not exist
            [
                [
                    'combinedFields' => '\\Foo\\Bar\\No\\Exist',
                    'fieldValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldValue\\MixedFieldValue',
                    'fieldToDb' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldToDb\\StringFieldToDb',
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'searchOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOptions\\StringSearchOptions',
                    'selectOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SelectOptions\\NullSelectOptions',
                    'renderInput' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderInput\\StringRenderer',
                    'renderValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\StringRenderer',
                    'renderName' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',

                ],
            ],
            // all good, but combinedFields class is empty
            [
                [
                    'combinedFields' => ' ',
                    'fieldValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldValue\\MixedFieldValue',
                    'fieldToDb' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldToDb\\StringFieldToDb',
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'searchOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOptions\\StringSearchOptions',
                    'selectOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SelectOptions\\NullSelectOptions',
                    'renderInput' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderInput\\StringRenderer',
                    'renderValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\StringRenderer',
                    'renderName' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
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
                    'renderInput' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderInput\\StringRenderer',
                    'renderValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\StringRenderer',
                    'renderName' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
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
     * @dataProvider validProvidersProvider
     * @expectedException \InvalidArgumentException
     */
    public function testGetProviderMissingException($providers)
    {
        $configInstance = new Config('foo');
        $configInstance->setProviders($providers);
        $undefinedProvider = $configInstance->getProvider('this provider does not exist in providers list');
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

    public function testGetTable()
    {
        $configInstance = new Config('field');
        $result = $configInstance->getTable();
        $this->assertTrue($result instanceof Table, "Config table returned a non-valid instance");
    }

    public function testSetOptions()
    {
        $options = [
            'foo' => 'bar',
            'blah' => 'yes',
            15 => 200,
            'a' => true,
        ];
        $configInstance = new Config('options');
        $configInstance->setOptions($options);
        $result = $configInstance->getOptions();
        $this->assertEquals($options, $result, "Config options were modified");
    }
}

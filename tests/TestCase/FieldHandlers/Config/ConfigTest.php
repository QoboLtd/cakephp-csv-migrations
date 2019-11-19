<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Config;

use Cake\ORM\Table;
use CsvMigrations\FieldHandlers\Config\Config;
use CsvMigrations\FieldHandlers\Config\ConfigInterface;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function invalidProvidersProvider(): array
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
                    'dbFieldType' => '\\CsvMigrations\\FieldHandlers\\Provider\\DbFieldType\\StringDbFieldType',
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
                    'combinedFields' => '\\stdClass',
                    'dbFieldType' => '\\CsvMigrations\\FieldHandlers\\Provider\\DbFieldType\\StringDbFieldType',
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
                    'dbFieldType' => '\\CsvMigrations\\FieldHandlers\\Provider\\DbFieldType\\StringDbFieldType',
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
                    'dbFieldType' => '\\CsvMigrations\\FieldHandlers\\Provider\\DbFieldType\\StringDbFieldType',
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
     * @return mixed[]
     */
    public function validProvidersProvider(): array
    {
        return [
            [
                [
                    'combinedFields' => '\\CsvMigrations\\FieldHandlers\\Provider\\CombinedFields\\NullCombinedFields',
                    'dbFieldType' => '\\CsvMigrations\\FieldHandlers\\Provider\\DbFieldType\\StringDbFieldType',
                    'fieldValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldValue\\MixedFieldValue',
                    'fieldToDb' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldToDb\\StringFieldToDb',
                    'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
                    'searchOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOptions\\StringSearchOptions',
                    'selectOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SelectOptions\\NullSelectOptions',
                    'renderInput' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderInput\\StringRenderer',
                    'renderValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\StringRenderer',
                    'renderName' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
                    'validationRules' => '\\CsvMigrations\\FieldHandlers\\Provider\\ValidationRules\\StringValidationRules',
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $providers
     * @dataProvider invalidProvidersProvider
     * @expectedException \InvalidArgumentException
     */
    public function testSetProvidersException(array $providers): void
    {
        $configInstance = new Config('foo');
        $configInstance->setProviders($providers);
    }

    /**
     * @param mixed[] $providers
     * @dataProvider invalidProvidersProvider
     * @expectedException \InvalidArgumentException
     */
    public function testValidateProvidersException(array $providers): void
    {
        $configInstance = new Config('foo');
        $configInstance->validateProviders($providers);
    }

    public function testConstruct(): void
    {
        $configInstance = new Config('foo');
        $this->assertTrue(is_object($configInstance), "Failed to instantiate Config object");
        $this->assertTrue($configInstance instanceof ConfigInterface, "Config instance does not implement ConfigInterface");
    }

    /**
     * @param mixed[] $providers
     * @dataProvider validProvidersProvider
     */
    public function testGetProviders(array $providers): void
    {
        $configInstance = new Config('foo');
        $configInstance->setProviders($providers);
        $actualProviders = $configInstance->getProviders();
        $this->assertEquals($providers, $actualProviders, "Providers did not return provided list");
    }

    /**
     * @param mixed[] $providers
     * @dataProvider validProvidersProvider
     * @expectedException \InvalidArgumentException
     */
    public function testGetProviderMissingException(array $providers): void
    {
        $configInstance = new Config('foo');
        $configInstance->setProviders($providers);
        $undefinedProvider = $configInstance->getProvider('this provider does not exist in providers list');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetFieldExceptionEmptyString(): void
    {
        $configInstance = new Config('   ');
    }

    public function testGetTable(): void
    {
        $configInstance = new Config('field');
        $result = $configInstance->getTable();
        $this->assertTrue($result instanceof Table, "Config table returned a non-valid instance");
    }

    public function testSetOptions(): void
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

<?php
namespace CsvMigrations\Test\TestCase;

use CsvMigrations\ConfigurationTrait;
use PHPUnit_Framework_TestCase;

class ConfigurationTraitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->mock = $this->getMockForTrait(ConfigurationTrait::class);
    }

    public function testIsSearchable()
    {
        $this->assertFalse($this->mock->isSearchable());
        $this->assertFalse($this->mock->isSearchable(0));
        $this->assertFalse($this->mock->isSearchable(null));

        $this->assertTrue($this->mock->isSearchable(true));
        $this->assertTrue($this->mock->isSearchable(1));
        $this->assertTrue($this->mock->isSearchable('foobar'));
    }

    public function testModuleAlias()
    {
        $this->assertSame($this->mock->moduleAlias('foo'), 'foo');
        $this->assertSame($this->mock->moduleAlias(), 'foo');
    }

    public function testHiddenAssociations()
    {
        $this->assertSame($this->mock->hiddenAssociations('Foo,Bar'), ['Foo', 'Bar']);
    }

    public function testAssociationLabels()
    {
        $data = [
            'associationLabels' => [
                'Foo' => 'Bar'
            ]
        ];

        $this->assertSame(
            $this->mock->associationLabels($data['associationLabels']),
            ['Foo' => 'Bar'],
            "Association Labels are parsed incorrectly"
        );
    }

    public function testAssociationLabelsSpecialSymbols()
    {
        $data = ['associationLabels' => [ 'EntityIdTable' => "Super Uper ()"]];

        $this->assertSame(
            $this->mock->associationLabels($data['associationLabels']),
            ['EntityIdTable' => 'Super Uper ()'],
            "Special symbols cannot be parsed properly"
        );
    }

    public function testParentField()
    {
        $this->assertSame(
            $this->mock->parentField('Bar'),
            'Bar',
            "Parent field is not passed properly into setter"
        );
    }

    public function testLookupFields()
    {
        $this->assertSame(
            $this->mock->lookupFields(),
            null,
            "Default lookupField is not set yet"
        );

        $this->assertSame(
            $this->mock->lookupFields('foo,bar'),
            ['foo', 'bar'],
            "Incorrect setting of lookUp fields"
        );
    }

    public function testVirtualFields()
    {
        $this->assertEquals(
            $this->mock->setVirtualFields(),
            null,
            "Incorrect default value"
        );

        $data = [
            'virtualFields' => [
                'name' => 'company_name,first_name,last_name'
            ]
        ];

        $this->mock->setVirtualFields($data['virtualFields']);

        $this->assertSame(
            $this->mock->getVirtualFields(),
            ['name' => ['company_name', 'first_name', 'last_name']],
            'Incorrect Virtual Fields setting'
        );
    }

    public function testTypeaheadFields()
    {
        $this->assertSame(
            $this->mock->typeaheadFields(),
            null,
            "Incorrect default value"
        );

        $this->assertSame(
            $this->mock->typeaheadFields('first_name,last_name'),
            ['first_name', 'last_name'],
            "Incorrect values passed"
        );
    }
}

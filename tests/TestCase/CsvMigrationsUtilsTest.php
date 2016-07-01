<?php
namespace CsvMigrations\Test\TestCase;

use CsvMigrations\CsvMigrationsUtils;
use PHPUnit_Framework_TestCase;

class CsvMigrationsUtilsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider associationNameProvider
     */
    public function testCreateAssociationName($expected, $module, $foreignKey)
    {
        $this->assertEquals($expected, CsvMigrationsUtils::createAssociationName($module, $foreignKey));
    }

    public function associationNameProvider()
    {
        return [
            ['BarFoo', 'Foo', 'bar'],
            ['KeyFoobar', 'Foobar', 'key'],
            ['Foobar', 'Foobar', ''],
            ['KeyFooBar', 'Foo.Bar', 'key'],
            ['ForeignKeyFooBar', 'Foo.Bar', 'foreign_key'],
            ['KeyFooBar', 'Vendor/Foo.Bar', 'key']
        ];
    }
}

<?php
namespace CsvMigrations\Test\TestCase;

use CsvMigrations\CsvMigrationsUtils;
use PHPUnit_Framework_TestCase;

class CsvMigrationsUtilsTest extends PHPUnit_Framework_TestCase
{
    public function testCreateAssociationName()
    {
        $result = CsvMigrationsUtils::createAssociationName('Foo', 'bar');
        $this->assertEquals('BarFoo', $result);

        $result = CsvMigrationsUtils::createAssociationName('Foobar', 'key');
        $this->assertEquals('KeyFoobar', $result);

        $result = CsvMigrationsUtils::createAssociationName('Foo.Bar', 'key');
        $this->assertEquals('KeyFooBar', $result);

        $result = CsvMigrationsUtils::createAssociationName('Foo.Bar', 'foreign_key');
        $this->assertEquals('ForeignKeyFooBar', $result);
    }
}

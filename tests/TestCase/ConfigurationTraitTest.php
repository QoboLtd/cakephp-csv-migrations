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
}
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
}

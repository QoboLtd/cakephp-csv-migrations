<?php
namespace CsvMigrations\Test\TestCase\Utility\Validate\Check;

use Cake\TestSuite\TestCase;
use CsvMigrations\Utility\Validate\Check\CheckInterface;
use CsvMigrations\Utility\Validate\Check\MigrationCheck;

/**
 * CsvMigrations\Utility\Validate\Check\MigrationCheck Test Case
 */
class MigrationCheckTest extends TestCase
{
    /** @var \CsvMigrations\Utility\Validate\Check\MigrationCheck */
    protected $check;

    public function setUp() : void
    {
        $this->check = new MigrationCheck();
    }

    public function testConstruct() : void
    {
        $this->assertTrue($this->check instanceof CheckInterface, "MigrationCheck class does not implement CheckInterface");
    }

    public function testRun() : void
    {
        $result = $this->check->run('Users');
        $this->assertTrue(is_int($result), "run() returned a non-integer result");
    }

    public function testRunMissingName(): void
    {
        $this->check->run('Foo', ['configFile' => 'missing_name_migration.json']);
        $errors = $this->check->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertTrue(count($errors) >= 2, 'Expected 2 or more errors');
        $this->assertEquals("[Foo][migration] parse : [missing_name_migration.json] : Failed to validate json data against the schema.", $errors[0]);
        $this->assertContains('The property name is required', $errors[1]);
    }

    public function testRunDuplicatedName(): void
    {
        $this->check->run('Foo', ['configFile' => 'duplicated_name_migration.json']);
        $errors = $this->check->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertEquals("Foo migration specifies field 'id' more than once", $errors[0]);
    }

    public function testGetWarnings() : void
    {
        $result = $this->check->run('Users');
        $result = $this->check->getWarnings();
        $this->assertTrue(is_array($result), "getWarnings() returned a non-array result");
    }

    public function testGetErrors() : void
    {
        $result = $this->check->run('Users');
        $result = $this->check->getErrors();
        $this->assertTrue(is_array($result), "getErrors() returned a non-array result");
    }
}

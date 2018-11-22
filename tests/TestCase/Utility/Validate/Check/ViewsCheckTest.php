<?php
namespace CsvMigrations\Test\TestCase\Utility\Validate\Check;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CsvMigrations\Utility\Validate\Check\CheckInterface;
use CsvMigrations\Utility\Validate\Check\ViewsCheck;

/**
 * CsvMigrations\Utility\Validate\Check\ViewsCheck Test Case
 */
class ViewsCheckTest extends TestCase
{
    /** @var \CsvMigrations\Utility\Validate\Check\ViewsCheck */
    protected $check;

    public function setUp() : void
    {
        $this->check = new ViewsCheck();
    }

    public function testConstruct() : void
    {
        $this->assertTrue($this->check instanceof CheckInterface, "ViewsCheck class does not implement CheckInterface");
    }

    public function testRun() : void
    {
        $result = $this->check->run('Users');
        $this->assertTrue(is_int($result), "run() returned a non-integer result");
    }

    public function testRunTooManyColumns() : void
    {
        Configure::write('CsvMigrations.actions', ['too_many_columns']);
        $this->check->run('Foo', ['configFile' => 'missing_name_migration.json']);
        $errors = $this->check->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertEquals("[Foo][view] parse : [too_many_columns.json] : Validation failed", $errors[0]);
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

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

    /**
     * {@inheritDoc}
     */
    public function setUp() : void
    {
        $this->check = new ViewsCheck();
    }

    /**
     * Test the constructor
     */
    public function testConstruct() : void
    {
        $this->assertTrue($this->check instanceof CheckInterface, "ViewsCheck class does not implement CheckInterface");
    }

    /**
     * Test that it runs and the return is integer
     */
    public function testRun() : void
    {
        $result = $this->check->run('Users');
        $this->assertTrue(is_int($result), "run() returned an integer result");
    }

    /**
     * Test that the fields are not empty
     */
    public function testRunNonEmpty() : void
    {
        $result = $this->check->run('Foo');
        $this->assertTrue(is_int($result), "run() returned a non-integer result");
    }

    /**
     * Test when there are no fields
     */
    public function testRunFieldsEmpty() : void
    {
        Configure::write('CsvMigrations.actions', ['no_columns']);
        $result = $this->check->run('Foo');
        $this->assertTrue(is_int($result), "run() returned a non-integer result");
    }

    /**
     * Test when there is at least one unkown field
     */
    public function testRunUnkownFieldFromMany() : void
    {
        Configure::write('CsvMigrations.actions', ['unkown_field_many']);
        $result = $this->check->run('Foo');
        $this->assertTrue(is_int($result), "run() returned a non-integer result");
    }

    /**
     * Test when there is only one field and its unknown
     */
    public function testRunUnkownField() : void
    {
        Configure::write('CsvMigrations.actions', ['unkown_field']);
        $result = $this->check->run('Foo');
        $this->assertTrue(is_int($result), "run() returned a non-integer result");
    }

    /**
     * Test scenario when fields are embedded
     */
    public function testRunEmbedded() : void
    {
        Configure::write('CsvMigrations.actions', ['embedded']);
        $result = $this->check->run('Foo');
        $this->assertTrue(is_int($result), "run() returned a non-integer result");
    }

    /**
     * Test that there are too many columns in fields array
     */
    public function testRunTooManyColumns() : void
    {
        Configure::write('CsvMigrations.actions', ['too_many_columns']);
        $this->check->run('Foo', ['configFile' => 'missing_name_migration.json']);
        $errors = $this->check->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertEquals("[Foo][view] parse : [too_many_columns.json] : Validation failed", $errors[0]);
    }

    /**
     * Test that that the run returns warnings
     */
    public function testGetWarnings() : void
    {
        $result = $this->check->run('Users');
        $result = $this->check->getWarnings();
        $this->assertTrue(is_array($result), "getWarnings() returned a non-array result");
    }

    /**
     * Test that the run returns errors
     */
    public function testGetErrors() : void
    {
        $result = $this->check->run('Users');
        $result = $this->check->getErrors();
        $this->assertTrue(is_array($result), "getErrors() returned a non-array result");
    }
}

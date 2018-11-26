<?php
namespace CsvMigrations\Test\TestCase\Utility\Validate\Check;

use Cake\TestSuite\TestCase;
use CsvMigrations\Utility\Validate\Check\CheckInterface;
use CsvMigrations\Utility\Validate\Check\ConfigCheck;

/**
 * CsvMigrations\Utility\Validate\Check\ConfigCheck Test Case
 */
class ConfigCheckTest extends TestCase
{
    protected $check;

    public function setUp() : void
    {
        $this->check = new ConfigCheck();
    }

    public function testConstruct() : void
    {
        $this->assertTrue($this->check instanceof CheckInterface, "ConfigCheck class does not implement CheckInterface");
    }

    public function testRun() : void
    {
        $result = $this->check->run('Users');
        $this->assertTrue(is_int($result), "run() returned a non-integer result");
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

    /**
     * Test an invalid module
     */
    public function testInvalidModule() : void
    {
        $result = $this->check->run('Users1');
        $result = $this->check->getErrors();
        $this->assertTrue(is_array($result), "getErrors() returned a non-array result");
    }

    /**
     * Test an invalid configuration module file
     */
    public function testInvalidConfig() : void
    {
        $result = $this->check->run('Books', [ 'icon_bad_values' => ['cube'], 'display_field_bad_values' => ["title2"] ]);
        $result = $this->check->getErrors();
        $this->assertTrue(is_array($result), "getErrors() returned a non-array result");
    }
}

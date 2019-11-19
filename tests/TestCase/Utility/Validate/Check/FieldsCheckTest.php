<?php

namespace CsvMigrations\Test\TestCase\Utility\Validate\Check;

use Cake\TestSuite\TestCase;
use CsvMigrations\Utility\Validate\Check\CheckInterface;
use CsvMigrations\Utility\Validate\Check\FieldsCheck;

/**
 * CsvMigrations\Utility\Validate\Check\FieldsCheck Test Case
 */
class FieldsCheckTest extends TestCase
{
    protected $check;

    public function setUp(): void
    {
        $this->check = new FieldsCheck();
    }

    public function testConstruct(): void
    {
        $this->assertTrue($this->check instanceof CheckInterface, "FieldsCheck class does not implement CheckInterface");
    }

    public function testRun(): void
    {
        $result = $this->check->run('Users');
        $this->assertTrue(is_int($result), "run() returned a non-integer result");
    }

    public function testGetWarnings(): void
    {
        $result = $this->check->run('Users');
        $result = $this->check->getWarnings();
        $this->assertTrue(is_array($result), "getWarnings() returned a non-array result");
    }

    public function testGetErrors(): void
    {
        $result = $this->check->run('Users');
        $result = $this->check->getErrors();
        $this->assertTrue(is_array($result), "getErrors() returned a non-array result");
    }
}

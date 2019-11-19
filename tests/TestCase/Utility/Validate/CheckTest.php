<?php

namespace CsvMigrations\Test\TestCase\Utility\Validate;

use Cake\TestSuite\TestCase;
use CsvMigrations\Utility\Validate\Check;
use CsvMigrations\Utility\Validate\Check\CheckInterface;

/**
 * CsvMigrations\Utility\Validate\Check Test Case
 */
class CheckTest extends TestCase
{
    public function testGetInstance(): void
    {
        $checks = Check::getList('Users');
        // This is just a safety net in case test setup changes
        // and returns no checks for Users module, we should notice
        // it here, separately from the testGetList().
        $this->assertTrue(is_array($checks), "getList() returned a non-array result");
        $this->assertFalse(empty($checks), "getList() returned an empty result");

        foreach ($checks as $class => $options) {
            $result = Check::getInstance($class);
            $this->assertTrue(is_object($result), "getInstance() returned a non-object result");
            $this->assertTrue($result instanceof CheckInterface, "getInstance() returned a wrong interface for class [$class]");
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetInstanceExceptionMissingClass(): void
    {
        $result = Check::getInstance('this class does not exist');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetInstanceExceptionBadInterface(): void
    {
        $result = Check::getInstance(__CLASS__);
    }

    public function testGetList(): void
    {
        $result = Check::getList('Users');
        $this->assertTrue(is_array($result), "getList() returned a non-array result");
        $this->assertFalse(empty($result), "getList() returned an empty result");
    }
}

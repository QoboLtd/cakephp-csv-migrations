<?php

namespace CsvMigrations\Test\TestCase\Utility\Validate;

use Cake\TestSuite\TestCase;
use CsvMigrations\Utility\Validate\Utility;

/**
 * CsvMigrations\Utility\Validate\Utility Test Case
 */
class UtilityTest extends TestCase
{
    public function testGetModules(): void
    {
        $result = Utility::getModules();
        $this->assertTrue(is_array($result), "getModules() returned a non-array result");
        $this->assertFalse(empty($result), "getModules() returned an empty result");
    }

    public function testIsValidModule(): void
    {
        $modules = Utility::getModules();
        // This is just a safety net in case test setup changes
        // and returns no module, we should notice
        // it here, separately from the testGetModules().
        $this->assertTrue(is_array($modules), "getModules() returned a non-array result");
        $this->assertFalse(empty($modules), "getModules() returned an empty result");

        foreach ($modules as $module) {
            $result = Utility::isValidModule($module);
            $this->assertTrue(is_bool($result), "isValidModule() returned a non-boolean result");
            $this->assertTrue($result, "isValidModule() returned false for a valid module");
        }

        $result = Utility::isValidModule('this is not a valid module');
        $this->assertTrue(is_bool($result), "isValidModule() returned a non-boolean result");
        $this->assertFalse($result, "isValidModule() returned true for a non-valid module");
    }

    public function testIsValidList(): void
    {
        $result = Utility::isValidList('currencies');
        $this->assertTrue(is_bool($result), "isValidList() returned a non-boolean result");
        $this->assertTrue($result, "isValidList() returned false for a valid list");

        $result = Utility::isValidList('Common.currencies');
        $this->assertTrue(is_bool($result), "isValidList() returned a non-boolean result");
        $this->assertTrue($result, "isValidList() returned false for a valid list");

        $result = Utility::isValidList('this list does not exist');
        $this->assertTrue(is_bool($result), "isValidList() returned a non-boolean result");
        $this->assertFalse($result, "isValidList() returned true for a non-valid list");
    }

    public function testIsRealModuleField(): void
    {
        $result = Utility::isRealModuleField('Users', 'id');
        $this->assertTrue(is_bool($result), "isRealModuleField() returned a non-boolean result");
    }

    public function testIsVirtualModuleField(): void
    {
        $result = Utility::isVirtualModuleField('Users', 'id');
        $this->assertTrue(is_bool($result), "isVirtualModuleField() returned a non-boolean result");
    }

    public function testIsValidModuleField(): void
    {
        $result = Utility::isValidModuleField('Users', 'id');
        $this->assertTrue(is_bool($result), "isValidModuleField() returned a non-boolean result");
    }

    public function testIsValidFieldType(): void
    {
        $result = Utility::isValidFieldType('uuid');
        $this->assertTrue(is_bool($result), "isValidFieldType() returned a non-boolean result");
        $this->assertTrue($result, "isValidFieldType() returned false for a valid field type");

        $result = Utility::isValidFieldType('this field type does not exist');
        $this->assertTrue(is_bool($result), "isValidFieldType() returned a non-boolean result");
        $this->assertFalse($result, "isValidFieldType() returned true for a non-valid field type");
    }
}

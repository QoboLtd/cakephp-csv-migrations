<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandler;
use PHPUnit\Framework\TestCase;

class BooleanFieldHandlerTest extends TestCase
{
    protected $table = 'fields';
    protected $field = 'field_boolean';
    protected $type = 'boolean';

    protected $fh;

    protected function setUp(): void
    {
        $config = ConfigFactory::getByType($this->type, $this->field, $this->table);
        $this->fh = new FieldHandler($config);
    }

    public function testRenderInput(): void
    {
        $result = $this->fh->renderInput('');

        $this->assertContains('name="' . $this->table . '[' . $this->field . ']"', $result);
        $this->assertContains('type="hidden"', $result);
        $this->assertContains('type="checkbox"', $result);
        $this->assertContains('value="0"', $result);
        $this->assertContains('value="1"', $result);
    }

    public function testFieldToDb(): void
    {
        $csvField = new CsvField(['name' => $this->field, 'type' => $this->type]);
        $fh = $this->fh;
        $result = $fh::fieldToDb($csvField);

        $this->assertTrue(is_array($result), "fieldToDb() did not return an array");
        $this->assertFalse(empty($result), "fieldToDb() returned an empty array");
        $this->assertTrue(array_key_exists($this->field, $result), "fieldToDb() did not return field key");
        $this->assertTrue(is_object($result[$this->field]), "fieldToDb() did not return object value for field key");
        $this->assertTrue(is_a($result[$this->field], 'CsvMigrations\FieldHandlers\DbField'), "fieldToDb() did not return DbField instance for field key");

        $this->assertEquals($this->fh->getDbFieldType(), $result[$this->field]->getType(), "fieldToDb() did not return correct type for DbField instance");
        $this->assertEquals($this->type, $result[$this->field]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");
    }

    /**
     * @return mixed[]
     */
    public function getValues(): array
    {
        return [
            [null, 'No', 'Null'],
            ['', 'No', 'Empty string'],
            [1, 'Yes', 'Integer true'],
            [0, 'No', 'Integer false'],
            ['1', 'Yes', 'String true'],
            ['0', 'No', 'String false'],
        ];
    }

    /**
     * @dataProvider getValues
     * @param mixed $value
     */
    public function testRenderValue($value, string $expected, string $description): void
    {
        $result = $this->fh->renderValue($value, []);
        $this->assertEquals($expected, $result, "Value rendering is broken for: $description");
    }

    public function testGetSearchOptions(): void
    {
        $result = $this->fh->getSearchOptions();

        $this->assertTrue(is_array($result), "getSearchOptions() did not return an array");
        $this->assertFalse(empty($result), "getSearchOptions() returned an empty result");

        $this->assertArrayHasKey($this->field, $result, "getSearchOptions() did not return field key");

        $this->assertArrayHasKey('type', $result[$this->field], "getSearchOptions() did not return 'type' key");
        $this->assertArrayHasKey('label', $result[$this->field], "getSearchOptions() did not return 'label' key");
        $this->assertArrayHasKey('operators', $result[$this->field], "getSearchOptions() did not return 'operators' key");
        $this->assertArrayHasKey('input', $result[$this->field], "getSearchOptions() did not return 'input' key");

        $this->assertArrayHasKey('is', $result[$this->field]['operators'], "getSearchOptions() did not return 'is' operator");
        $this->assertArrayHasKey('is_not', $result[$this->field]['operators'], "getSearchOptions() did not return 'is_not' operator");
    }
}

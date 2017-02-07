<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\TextFieldHandler;
use PHPUnit_Framework_TestCase;

class TextFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $table = 'Fields';
    protected $field = 'field_text';

    protected $fh;

    protected function setUp()
    {
        $this->fh = new TextFieldHandler($this->table, $this->field);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->fh));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\FieldHandlerInterface', $implementedInterfaces), "FieldHandlerInterface is not implemented");
    }

    public function testFieldToDb()
    {
        $csvField = new CsvField(['name' => $this->field, 'type' => 'text']);
        $fh = $this->fh;
        $result = $fh::fieldToDb($csvField);

        $this->assertTrue(is_array($result), "fieldToDb() did not return an array");
        $this->assertFalse(empty($result), "fieldToDb() returned an empty array");
        $this->assertTrue(array_key_exists($this->field, $result), "fieldToDb() did not return field key");
        $this->assertTrue(is_object($result[$this->field]), "fieldToDb() did not return object value for field key");
        $this->assertTrue(is_a($result[$this->field], 'CsvMigrations\FieldHandlers\DbField'), "fieldToDb() did not return DbField instance for field key");

        $this->assertEquals(TextFieldHandler::DB_FIELD_TYPE, $result[$this->field]->getType(), "fieldToDb() did not return correct type for DbField instance");
        $this->assertEquals('text', $result[$this->field]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");
        $this->assertGreaterThan(100000000, $result[$this->field]->getLimit(), "fieldToDb() did not return correct limit for DbField instance");
    }

    public function getValues()
    {
        return [
            [true, "<p>1</p>\n", 'Boolean true'],
            [false, '', 'Boolean false'],
            [0, 0, 'Integer zero'],
            [1, "<p>1</p>\n", 'Positive integer'],
            [-1, "<p>-1</p>\n", 'Negative integer'],
            [1.501, "<p>1.501</p>\n", 'Positive float'],
            [-1.501, "<p>-1.501</p>\n", 'Negative float'],
            ['', '', 'Empty string'],
            ['foobar', "<p>foobar</p>\n", 'String'],
            ['2017-07-05', "<p>2017-07-05</p>\n", 'Date'],
        ];
    }

    /**
     * @dataProvider getValues
     */
    public function testRenderValue($value, $expected, $description)
    {
        $result = $this->fh->renderValue($value, []);
        $this->assertEquals($expected, $result, "Value rendering is broken for: $description");
    }

    public function testRenderValueWithPlainFlag()
    {
        $result = $this->fh->renderValue('Hello World!', ['renderAs' => 'plain']);
        $this->assertEquals('Hello World!', $result);
    }

    public function testGetSearchOptions()
    {
        $result = $this->fh->getSearchOptions();

        $this->assertTrue(is_array($result), "getSearchOptions() did not return an array");
        $this->assertFalse(empty($result), "getSearchOptions() returned an empty result");

        $this->assertArrayHasKey($this->field, $result, "getSearchOptions() did not return field key");

        $this->assertArrayHasKey('type', $result[$this->field], "getSearchOptions() did not return 'type' key");
        $this->assertArrayHasKey('label', $result[$this->field], "getSearchOptions() did not return 'label' key");
        $this->assertArrayHasKey('operators', $result[$this->field], "getSearchOptions() did not return 'operators' key");
        $this->assertArrayHasKey('input', $result[$this->field], "getSearchOptions() did not return 'input' key");

        $this->assertArrayHasKey('contains', $result[$this->field]['operators'], "getSearchOptions() did not return 'contains' operator");
        $this->assertArrayHasKey('not_contains', $result[$this->field]['operators'], "getSearchOptions() did not return 'not_contains' operator");
        $this->assertArrayHasKey('starts_with', $result[$this->field]['operators'], "getSearchOptions() did not return 'starts_with' operator");
        $this->assertArrayHasKey('ends_with', $result[$this->field]['operators'], "getSearchOptions() did not return 'ends_with' operator");
    }
}

<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DecimalFieldHandler;
use CsvMigrations\FieldHandlers\ListFieldHandler;
use CsvMigrations\FieldHandlers\MetricFieldHandler;
use PHPUnit_Framework_TestCase;

class MetricFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $table = 'Fields';
    protected $field = 'field_metric';

    protected $fh;

    protected function setUp()
    {
        $this->fh = new MetricFieldHandler($this->table, $this->field);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->fh));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\FieldHandlerInterface', $implementedInterfaces), "FieldHandlerInterface is not implemented");
    }

    public function testFieldToDb()
    {
        $csvField = new CsvField(['name' => $this->field, 'type' => 'text']);
        $result = $this->fh->fieldToDb($csvField);

        $this->assertTrue(is_array($result), "fieldToDb() did not return an array");
        $this->assertFalse(empty($result), "fieldToDb() returned an empty array");

        $fieldName = $this->field . '_' . 'amount';
        $this->assertTrue(array_key_exists($fieldName, $result), "fieldToDb() did not return field key");
        $this->assertTrue(is_object($result[$fieldName]), "fieldToDb() did not return object value for field key");
        $this->assertTrue(is_a($result[$fieldName], 'CsvMigrations\FieldHandlers\DbField'), "fieldToDb() did not return DbField instance for field key");

        $this->assertEquals(DecimalFieldHandler::DB_FIELD_TYPE, $result[$fieldName]->getType(), "fieldToDb() did not return correct type for DbField instance");
        $this->assertEquals('decimal', $result[$fieldName]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");

        $fieldName = $this->field . '_' . 'unit';
        $this->assertTrue(array_key_exists($fieldName, $result), "fieldToDb() did not return field key");
        $this->assertTrue(is_object($result[$fieldName]), "fieldToDb() did not return object value for field key");
        $this->assertTrue(is_a($result[$fieldName], 'CsvMigrations\FieldHandlers\DbField'), "fieldToDb() did not return DbField instance for field key");

        $this->assertEquals(ListFieldHandler::DB_FIELD_TYPE, $result[$fieldName]->getType(), "fieldToDb() did not return correct type for DbField instance");
        $this->assertEquals('string', $result[$fieldName]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");
        $this->assertEquals(255, $result[$fieldName]->getLimit(), "fieldToDb() did not return correct limit for DbField instance");
    }

    public function testGetSearchOptions()
    {
        $result = $this->fh->getSearchOptions();
        $this->assertTrue(is_array($result), "getSearchOptions() did not return an array");
        $this->assertFalse(empty($result), "getSearchOptions() returned an empty result");
    }
}

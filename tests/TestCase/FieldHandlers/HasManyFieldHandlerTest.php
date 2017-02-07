<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\HasManyFieldHandler;
use PHPUnit_Framework_TestCase;

class HasManyFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $table = 'Fields';
    protected $field = 'field_hasmany';

    protected $fh;

    protected function setUp()
    {
        $this->fh = new HasManyFieldHandler($this->table, $this->field);
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

        $this->assertEquals(HasManyFieldHandler::DB_FIELD_TYPE, $result[$this->field]->getType(), "fieldToDb() did not return correct type for DbField instance");
        $this->assertEquals('uuid', $result[$this->field]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");
    }

    public function testGetSearchOptions()
    {
        $result = $this->fh->getSearchOptions();
        $this->assertTrue(is_array($result), "getSearchOptions() did not return an array");
        $this->assertTrue(empty($result), "getSearchOptions() returned a non-empty result");
    }
}

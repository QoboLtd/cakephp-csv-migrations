<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DbField;
use PHPUnit_Framework_TestCase;

class DbFieldTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $dbField = new DbField('field', 'string', 255, true, true, true);

        $this->assertEquals('field', $dbField->getName(), "Field name was not properly set");
        $this->assertEquals('string', $dbField->getType(), "Field type was not properly set");
        $this->assertEquals(255, $dbField->getLimit(), "Field limit was not properly set");
        $this->assertTrue($dbField->getRequired(), "Field required was not properly set");
        $this->assertTrue($dbField->getNonSearchable(), "Field non-searchable was not properly set");
        $this->assertTrue($dbField->getUnique(), "Field unique was not properly set");
    }

    public function testFromCsvField()
    {
        $csvField = new CsvField([
            'name' => 'field',
            'type' => 'string(255)',
            'required' => true,
            'non-searchable' => true,
            'unique' => true,
        ]);
        $result = DbField::fromCsvField($csvField);

        $this->assertTrue(is_object($result), "fromCsvField() returned a non-object");
        $this->asserttrue(is_a($result, 'CsvMigrations\FieldHandlers\DbField'), "fromCsvField() did not return a DbField instance");

        $this->assertEquals('field', $result->getName(), "fromCsvField() did not set correct name");
        $this->assertEquals('string', $result->getType(), "fromCsvField() did not set correct type");
        $this->assertEquals(255, $result->getLimit(), "fromCsvField() did not set correct limit");
        $this->assertEquals(true, $result->getRequired(), "fromCsvField() did not set correct required");
        $this->assertEquals(true, $result->getNonSearchable(), "fromCsvField() did not set correct non-searchable");
        $this->assertEquals(true, $result->getUnique(), "fromCsvField() did not set correct unique");
    }

    public function testSetOptions()
    {
        $dbField = new DbField('field', 'string', 255, true, true, true);
        $options = ['limit' => 100];
        $dbField->setOptions($options);
        $result = $dbField->getOptions();
        $this->assertEquals($options, $result, "Setting options is broken");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetNameEmptyException()
    {
        $dbField = new DbField('', 'string', 255, true, true, true);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetTypeEmptyException()
    {
        $dbField = new DbField('field', '', 255, true, true, true);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetTypeUnsupportedException()
    {
        $dbField = new DbField('field', 'unsupported-field-type', 255, true, true, true);
    }
}

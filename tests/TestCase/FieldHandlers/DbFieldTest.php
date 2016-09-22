<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

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

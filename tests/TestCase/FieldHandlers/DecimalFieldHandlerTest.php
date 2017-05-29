<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DecimalFieldHandler;
use PHPUnit_Framework_TestCase;

class DecimalFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $table = 'Fields';
    protected $field = 'field_decimal';

    protected $fh;

    protected function setUp()
    {
        $this->fh = new DecimalFieldHandler($this->table, $this->field);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->fh));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\FieldHandlerInterface', $implementedInterfaces), "FieldHandlerInterface is not implemented");
    }

    public function testFieldToDb()
    {
        $csvField = new CsvField(['name' => $this->field, 'type' => 'decimal(12.4)']);
        $fh = $this->fh;
        $result = $fh::fieldToDb($csvField);

        $this->assertTrue(is_array($result), "fieldToDb() did not return an array");
        $this->assertFalse(empty($result), "fieldToDb() returned an empty array");
        $this->assertTrue(array_key_exists($this->field, $result), "fieldToDb() did not return field key");
        $this->assertTrue(is_object($result[$this->field]), "fieldToDb() did not return object value for field key");
        $this->assertTrue(is_a($result[$this->field], 'CsvMigrations\FieldHandlers\DbField'), "fieldToDb() did not return DbField instance for field key");

        $this->assertEquals(DecimalFieldHandler::DB_FIELD_TYPE, $result[$this->field]->getType(), "fieldToDb() did not return correct type for DbField instance");
        $this->assertEquals('decimal', $result[$this->field]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");

        $options = $result[$this->field]->getOptions();
        $this->assertEquals('12.4', $options['limit']);
        $this->assertEquals('12', $options['precision']);
        $this->assertEquals('4', $options['scale']);
        $this->assertEquals(true, $options['null']);
    }

    public function testFieldToDbNoLimit()
    {
        $csvField = new CsvField(['name' => $this->field, 'type' => 'decimal']);
        $fh = $this->fh;
        $result = $fh::fieldToDb($csvField);
        $options = $result[$this->field]->getOptions();

        $this->assertArrayNotHasKey('limit', $options);
        $this->assertEquals('10', $options['precision']);
        $this->assertEquals('2', $options['scale']);
    }

    public function testFieldToDbWrongLimit()
    {
        $csvField = new CsvField(['name' => $this->field, 'type' => 'decimal(15)']);
        $fh = $this->fh;
        $result = $fh::fieldToDb($csvField);
        $options = $result[$this->field]->getOptions();

        $this->assertEquals('15', $options['limit']);
        $this->assertEquals('10', $options['precision']);
        $this->assertEquals('2', $options['scale']);
    }

    public function getValues()
    {
        return [
            [true, '1.00', 'Boolean true'],
            [false, '0', 'Boolean false'],
            [0, '0', 'Integer zero'],
            [1, '1.00', 'Positive integer'],
            [-1, '-1.00', 'Negative integer'],
            [1.50, '1.50', 'Positive float'],
            [-1.50, '-1.50', 'Negative float'],
            ['', '0', 'Empty string'],
            ['foobar', '0', 'String'],
            ['foobar15', '15', 'String with number'],
            ['2017-07-05', '2,017.00', 'Date'],
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

        $this->assertArrayHasKey('is', $result[$this->field]['operators'], "getSearchOptions() did not return 'is' operator");
        $this->assertArrayHasKey('is_not', $result[$this->field]['operators'], "getSearchOptions() did not return 'is_not' operator");
        $this->assertArrayHasKey('greater', $result[$this->field]['operators'], "getSearchOptions() did not return 'greater' operator");
        $this->assertArrayHasKey('less', $result[$this->field]['operators'], "getSearchOptions() did not return 'less' operator");
    }
}

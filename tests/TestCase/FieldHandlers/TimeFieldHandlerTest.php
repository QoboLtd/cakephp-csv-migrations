<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\I18n\Date;
use Cake\I18n\Time;
use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandler;
use PHPUnit\Framework\TestCase;

class TimeFieldHandlerTest extends TestCase
{
    protected $table = 'fields';
    protected $field = 'field_time';
    protected $type = 'time';

    protected $fh;

    protected function setUp() : void
    {
        $config = ConfigFactory::getByType($this->type, $this->field, $this->table);
        $this->fh = new FieldHandler($config);
    }

    public function testFieldToDb() : void
    {
        $csvField = new CsvField(['name' => $this->field, 'type' => $this->type]);
        $fh = $this->fh;
        $result = $fh::fieldToDb($csvField);

        $this->assertTrue(is_array($result), "fieldToDb() did not return an array");
        $this->assertFalse(empty($result), "fieldToDb() returned an empty array");
        $this->assertTrue(array_key_exists($this->field, $result), "fieldToDb() did not return field key");
        $this->assertTrue(is_object($result[$this->field]), "fieldToDb() did not return object value for field key");
        $this->assertTrue(is_a($result[$this->field], 'CsvMigrations\FieldHandlers\DbField'), "fieldToDb() did not return DbField instance for field key");

        $this->assertEquals($this->fh->getDbFieldType($this->field), $result[$this->field]->getType(), "fieldToDb() did not return correct type for DbField instance");
        $this->assertEquals($this->type, $result[$this->field]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");
    }

    /**
     * @return mixed[]
     */
    public function getValues() : array
    {
        return [
            ['2017-07-06 14:20:00', '2017-07-06 14:20:00', 'Date time string'],
            ['2017-07-06', '2017-07-06', 'Date string'],
            ['14:20:00', '14:20:00', 'Time string'],
            ['foobar', 'foobar', 'Non-date string'],
            [15, 15, 'Non-date integer'],
            [Time::parse('2017-07-06 14:20:00'), '14:20', 'Time from object'],
        ];
    }

    /**
     * @dataProvider getValues
     * @param mixed $value
     * @param mixed $expected
     */
    public function testRenderValue($value, $expected, string $description) : void
    {
        $result = $this->fh->renderValue($value, []);
        $this->assertEquals($expected, $result, "Value rendering is broken for: $description");
    }

    public function testRenderInput() : void
    {
        $result = $this->fh->renderInput('13:30:00');
        $this->assertContains('name="' . $this->table . '[' . $this->field . ']"', $result);
        $this->assertContains('data-provide="timepicker"', $result);
        $this->assertContains('value="13:30:00"', $result);
    }

    public function testRenderInputWithTimeObject() : void
    {
        $result = $this->fh->renderInput(new Time('13:30'));

        $this->assertContains('value="13:30"', $result);
    }

    public function testRenderInputWithDateObject() : void
    {
        $result = $this->fh->renderInput(new Date('13:30'));

        $this->assertContains('value="13:30"', $result);
    }

    public function testGetSearchOptions() : void
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

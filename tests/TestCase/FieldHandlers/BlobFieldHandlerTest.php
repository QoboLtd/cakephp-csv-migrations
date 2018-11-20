<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandler;
use PHPUnit\Framework\TestCase;

class BlobFieldHandlerTest extends TestCase
{
    protected $dataDir;
    protected $table = 'fields';
    protected $field = 'field_blob';
    protected $type = 'blob';

    protected $fh;

    protected function setUp() : void
    {
        $this->dataDir = dirname(dirname(__DIR__)) . DS . 'config' . DS . 'Modules' . DS;
        Configure::write('CsvMigrations.modules.path', $this->dataDir);
        $config = ConfigFactory::getByType($this->type, $this->field, $this->table);
        $this->fh = new FieldHandler($config);
    }

    public function testRenderInput() : void
    {
        $result = $this->fh->renderInput('');

        $this->assertContains('name="' . $this->table . '[' . $this->field . ']"', $result);
        $this->assertContains('<textarea', $result);
        $this->assertContains('</textarea>', $result);
    }

    public function testRenderInputWithResource() : void
    {
        $result = $this->fh->renderInput(fopen('https://www.google.com', 'r'));

        $this->assertContains('Google', $result);
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

        $this->assertEquals($this->fh->getDbFieldType(), $result[$this->field]->getType(), "fieldToDb() did not return correct type for DbField instance");
        $this->assertEquals($this->type, $result[$this->field]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");
        $this->assertGreaterThan(100000000, $result[$this->field]->getLimit(), "fieldToDb() did not return correct limit for DbField instance");
    }

    /**
     * @return mixed[]
     */
    public function getValues() : array
    {
        return [
            [true, 'Boolean true'],
            [false, 'Boolean false'],
            [0, 'Integer zero'],
            [1, 'Positive integer'],
            [-1, 'Negative integer'],
            [1.501, 'Positive float'],
            [-1.501, 'Negative float'],
            ['', 'Empty string'],
            ['foobar', 'String'],
            ['2017-07-05', 'Date'],
        ];
    }

    /**
     * @dataProvider getValues
     * @param mixed $value
     */
    public function testRenderValue($value, string $description) : void
    {
        $result = $this->fh->renderValue($value, []);
        $this->assertEquals($value, $result, "Value rendering is broken for: $description");
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

        $this->assertArrayHasKey('contains', $result[$this->field]['operators'], "getSearchOptions() did not return 'contains' operator");
        $this->assertArrayHasKey('not_contains', $result[$this->field]['operators'], "getSearchOptions() did not return 'not_contains' operator");
        $this->assertArrayHasKey('starts_with', $result[$this->field]['operators'], "getSearchOptions() did not return 'starts_with' operator");
        $this->assertArrayHasKey('ends_with', $result[$this->field]['operators'], "getSearchOptions() did not return 'ends_with' operator");
    }
}

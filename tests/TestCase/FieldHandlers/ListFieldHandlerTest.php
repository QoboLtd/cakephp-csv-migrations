<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\ListFieldHandler;
use PHPUnit_Framework_TestCase;

class ListFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $table = 'Fields';
    protected $field = 'field_list';

    protected $fh;

    protected function setUp()
    {
        $dir = dirname(__DIR__) . DS . '..' . DS . 'config' . DS . 'Modules' . DS;
        Configure::write('CsvMigrations.modules.path', $dir);

        $this->fh = new ListFieldHandler($this->table, $this->field);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->fh));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\FieldHandlerInterface', $implementedInterfaces), "FieldHandlerInterface is not implemented");
    }

    public function getRenderedValues()
    {
        return [
            ['', ''],
            ['cy', 'Cyprus'],
            ['usa', 'USA'],
            ['uk', 'United Kingdom'],
        ];
    }

    public function getInputValues()
    {
        return [
            ['', ' -- Please choose -- '],
            ['cy', 'Cyprus'],
            ['usa', 'USA'],
            ['uk', 'United Kingdom'],
        ];
    }

    /**
     * @dataProvider getRenderedValues
     */
    public function testRenderValue($value, $expected)
    {
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'list(countries)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ]);

        $result = $this->fh->renderValue($value, $options);

        $this->assertEquals($expected, $result);
    }

    public function testRenderValueNested()
    {
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'list(nested)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ]);

        $result = $this->fh->renderValue('first_level_1.second_level_1.third_level_1', $options);

        $this->assertEquals('First level 1 - Second level 1 -  - Third level 1', $result);
    }

    public function testRenderValueWithWrongValue()
    {
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'list(countries)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ]);

        $result = $this->fh->renderValue('non-existing-value', $options);

        $this->assertNotEquals('non-existing-value', $result);
    }

    public function testRenderValueEmptyData()
    {
        $result = $this->fh->renderValue('', []);

        $this->assertEquals('', $result);
    }

    public function testRenderValueSetListItems()
    {
        $result = $this->fh->renderValue('foo', ['listItems' => ['foo' => 'Foo']]);

        $this->assertEquals('Foo', $result);
    }

    public function testRenderValueWithPlainFlag()
    {
        $result = $this->fh->renderValue('foo', ['listItems' => ['foo' => 'Foo'], 'renderAs' => 'plain']);
        $this->assertEquals('foo', $result);
    }

    /**
     * @dataProvider getInputValues
     */
    public function testRenderInput($value, $label)
    {
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'list(countries)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ]);

        $result = $this->fh->renderInput(null, $options);

        $this->assertContains('"' . $value . '"', $result);
        $this->assertContains($label, $result);

        if ('uk' === $value) {
            $this->assertContains('"' . $value . '" selected="selected"', $result);
        }
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

        $this->assertEquals(ListFieldHandler::DB_FIELD_TYPE, $result[$this->field]->getType(), "fieldToDb() did not return correct type for DbField instance");
        $this->assertEquals('string', $result[$this->field]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");
        $this->assertEquals(255, $result[$this->field]->getLimit(), "fieldToDb() did not return correct limit for DbField instance");
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
    }
}

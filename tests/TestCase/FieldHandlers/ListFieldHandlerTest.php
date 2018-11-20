<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandler;
use PHPUnit\Framework\TestCase;

class ListFieldHandlerTest extends TestCase
{
    protected $table = 'fields';
    protected $field = 'field_list';
    protected $type = 'list';

    protected $fh;

    protected function setUp() : void
    {
        $dir = dirname(__DIR__) . DS . '..' . DS . 'config' . DS . 'Modules' . DS;
        Configure::write('CsvMigrations.modules.path', $dir);

        $config = ConfigFactory::getByType($this->type, $this->field, $this->table);
        $this->fh = new FieldHandler($config);
    }

    /**
     * @return mixed[]
     */
    public function getRenderedValues() : array
    {
        return [
            ['', ''],
            ['cy', 'Cyprus'],
            ['usa', 'USA'],
            ['uk', 'United Kingdom'],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getInputValues() : array
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
    public function testRenderValue(string $value, string $expected) : void
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

    public function testRenderValueNested() : void
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

    public function testRenderValueWithWrongValue() : void
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

    public function testRenderValueEmptyData() : void
    {
        $result = $this->fh->renderValue('', []);

        $this->assertEquals('', $result);
    }

    public function testRenderValueSetListItems() : void
    {
        $result = $this->fh->renderValue('foo', ['listItems' => ['foo' => 'Foo']]);

        $this->assertEquals('Foo', $result);
    }

    public function testRenderValueWithPlainFlag() : void
    {
        $result = $this->fh->renderValue('foo', ['listItems' => ['foo' => 'Foo'], 'renderAs' => 'plain']);
        $this->assertEquals('foo', $result);
    }

    /**
     * @dataProvider getInputValues
     */
    public function testRenderInput(string $value, string $label) : void
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
        $this->assertEquals('string', $result[$this->field]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");
        $this->assertEquals(255, $result[$this->field]->getLimit(), "fieldToDb() did not return correct limit for DbField instance");
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
    }
}

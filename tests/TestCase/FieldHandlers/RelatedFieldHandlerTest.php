<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\RelatedFieldHandler;

class RelatedFieldHandlerTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.foo'
    ];

    protected $table = 'Fields';
    protected $field = 'field_related';

    protected $fh;

    public function setUp()
    {
        $this->fh = new RelatedFieldHandler($this->table, $this->field);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->fh));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\FieldHandlerInterface', $implementedInterfaces), "FieldHandlerInterface is not implemented");
    }

    public function testRenderValue()
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'related(Foo)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ]);
        $result = $this->fh->renderValue($id, $options);

        $this->assertContains('/foo/view/' . $id, $result);

        $table = TableRegistry::get('Foo');
        $entity = $table->get($id);
        $fieldName = $table->displayField();
        $this->assertContains($entity->{$fieldName}, $result);
    }

    public function testRenderInput()
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'related(Foo)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ]);

        $result = $this->fh->renderInput($id, $options);

        $this->assertContains('value="' . $id . '"', $result);
        $this->assertContains('data-url="/api/foo/lookup.json"', $result);

        $table = TableRegistry::get('Foo');
        $entity = $table->get($id);
        $fieldName = $table->displayField();
        $this->assertContains($entity->{$fieldName}, $result);
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

        $this->assertEquals(RelatedFieldHandler::DB_FIELD_TYPE, $result[$this->field]->getType(), "fieldToDb() did not return correct type for DbField instance");
        $this->assertEquals('uuid', $result[$this->field]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");
    }

    public function testGetSearchOptions()
    {
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'related(Foo)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ]);

        $result = $this->fh->getSearchOptions($options);

        $this->assertTrue(is_array($result), "getSearchOptions() did not return an array");
        $this->assertFalse(empty($result), "getSearchOptions() returned an empty result");

        $this->assertArrayHasKey($this->field, $result, "getSearchOptions() did not return field key");

        $this->assertArrayHasKey('type', $result[$this->field], "getSearchOptions() did not return 'type' key");
        $this->assertArrayHasKey('label', $result[$this->field], "getSearchOptions() did not return 'label' key");
        $this->assertArrayHasKey('operators', $result[$this->field], "getSearchOptions() did not return 'operators' key");
        $this->assertArrayHasKey('input', $result[$this->field], "getSearchOptions() did not return 'input' key");

        $this->assertArrayHasKey('is', $result[$this->field]['operators'], "getSearchOptions() did not return 'contains' operator");
        $this->assertArrayHasKey('is_not', $result[$this->field]['operators'], "getSearchOptions() did not return 'not_contains' operator");
    }
}

<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandler;
use PHPUnit\Framework\TestCase;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class SublistFieldHandlerTest extends TestCase
{
    protected $table = 'fields';
    protected $field = 'field_sublist';
    protected $type = 'sublist';

    protected $fh;

    protected function setUp(): void
    {
        $config = ConfigFactory::getByType($this->type, $this->field, $this->table);
        $this->fh = new FieldHandler($config);
    }

    public function testRenderInput(): void
    {
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'list(countries)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false,
        ]);

        $result = $this->fh->renderInput('', $options);

        $this->assertContains('data-type="dynamic-select"', $result);
        $this->assertContains('data-target', $result);
        $this->assertContains('data-structure', $result);
        $this->assertContains('data-option-values', $result);
        $this->assertContains('data-selectors', $result);

        $mc = new ModuleConfig(ConfigType::LISTS(), '', 'countries');
        $config = json_encode($mc->parse());
        $config = false !== $config ? json_decode($config, true) : [];
        $items = isset($config['items']) ? $config['items'] : [];
        foreach ($items as $key => $item) {
            if ((bool)$item['inactive']) {
                $this->assertNotContains(h('"' . $key . '"'), $result);
                $this->assertNotContains(h('"' . $item['label'] . '"'), $result);
            } else {
                $this->assertContains(h('"' . $key . '"'), $result);
                $this->assertContains(h('"' . $item['label'] . '"'), $result);
            }
        }
    }

    public function testFieldToDb(): void
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
        $this->assertEquals('string', $result[$this->field]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");
        $this->assertEquals(255, $result[$this->field]->getLimit(), "fieldToDb() did not return correct limit for DbField instance");
    }

    public function testGetSearchOptions(): void
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

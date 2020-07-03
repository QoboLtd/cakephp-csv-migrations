<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\ORM\Entity;
use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandler;
use PHPUnit\Framework\TestCase;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class MoneyFieldHandlerTest extends TestCase
{
    protected $table = 'fields';
    protected $field = 'field_money';
    protected $type = 'money';

    protected $fh;

    protected function setUp(): void
    {
        $config = ConfigFactory::getByType($this->type, $this->field, $this->table);
        $this->fh = new FieldHandler($config);
    }

    public function testRenderValue(): void
    {
        $options['entity'] = new Entity(['field_money_amount' => 150, 'field_money_currency' => 'EUR']);
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'money(Common.currencies)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false,
        ]);

        $result = $this->fh->renderValue('', $options);

        $this->assertEquals('150.00&nbsp;<span title="Euro">€&nbsp;(EUR)</span>', $result);
    }

    public function testRenderInput(): void
    {
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'money(Common.countries)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false,
        ]);

        $result = $this->fh->renderInput('', $options);

        $mc = new ModuleConfig(ConfigType::LISTS(), '', 'countries');
        $config = $mc->parseToArray();
        $items = isset($config['items']) ? $config['items'] : [];
        foreach ($items as $key => $item) {
            if ((bool)$item['inactive']) {
                $this->assertNotContains('value="' . $key . '"', $result);
                $this->assertNotContains($item['label'], $result);
            } else {
                $this->assertContains('value="' . $key . '"', $result);
                $this->assertContains($item['label'], $result);
            }
        }
    }

    public function testFieldToDb(): void
    {
        $csvField = new CsvField(['name' => $this->field, 'type' => 'money(money)']);
        $fh = $this->fh;
        $result = $fh::fieldToDb($csvField);

        $this->assertTrue(is_array($result), "fieldToDb() did not return an array");
        $this->assertFalse(empty($result), "fieldToDb() returned an empty array");

        $fieldName = $this->field . '_' . 'amount';
        $this->assertTrue(array_key_exists($fieldName, $result), "fieldToDb() did not return field key");
        $this->assertTrue(is_object($result[$fieldName]), "fieldToDb() did not return object value for field key");
        $this->assertTrue(is_a($result[$fieldName], 'CsvMigrations\FieldHandlers\DbField'), "fieldToDb() did not return DbField instance for field key");

        $this->assertEquals('decimal', $result[$fieldName]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");

        $fieldName = $this->field . '_' . 'currency';
        $this->assertTrue(array_key_exists($fieldName, $result), "fieldToDb() did not return field key");
        $this->assertTrue(is_object($result[$fieldName]), "fieldToDb() did not return object value for field key");
        $this->assertTrue(is_a($result[$fieldName], 'CsvMigrations\FieldHandlers\DbField'), "fieldToDb() did not return DbField instance for field key");

        $this->assertEquals('string', $result[$fieldName]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");
        $this->assertEquals(255, $result[$fieldName]->getLimit(), "fieldToDb() did not return correct limit for DbField instance");
    }

    public function testGetSearchOptions(): void
    {
        $result = $this->fh->getSearchOptions();
        $this->assertTrue(is_array($result), "getSearchOptions() did not return an array");
        $this->assertFalse(empty($result), "getSearchOptions() returned an empty result");
    }
}

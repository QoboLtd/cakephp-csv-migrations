<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\ORM\Entity;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DecimalFieldHandler;
use CsvMigrations\FieldHandlers\ListFieldHandler;
use CsvMigrations\FieldHandlers\MoneyFieldHandler;
use PHPUnit_Framework_TestCase;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class MoneyFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $table = 'Fields';
    protected $field = 'field_money';

    protected $fh;

    protected function setUp()
    {
        $this->fh = new MoneyFieldHandler($this->table, $this->field);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->fh));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\FieldHandlerInterface', $implementedInterfaces), "FieldHandlerInterface is not implemented");
    }

    public function testRenderValue()
    {
        $options['entity'] = new Entity(['field_money_amount' => 150, 'field_money_currency' => 'eur']);
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'money(currencies)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ]);

        $result = $this->fh->renderValue(null, $options);

        $this->assertEquals('150.00&nbsp;EUR', $result);
    }

    public function testRenderInput()
    {
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'money(countries)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ]);

        $result = $this->fh->renderInput(null, $options);

        $mc = new ModuleConfig(ConfigType::LISTS(), null, 'countries');
        foreach ($mc->parse()->items as $item) {
            if ((bool)$item->inactive) {
                $this->assertNotContains('value="' . $item->value . '"', $result);
                $this->assertNotContains($item->label, $result);
            } else {
                $this->assertContains('value="' . $item->value . '"', $result);
                $this->assertContains($item->label, $result);
            }
        }
    }

    public function testFieldToDb()
    {
        $csvField = new CsvField(['name' => $this->field, 'type' => 'text']);
        $fh = $this->fh;
        $result = $fh::fieldToDb($csvField);

        $this->assertTrue(is_array($result), "fieldToDb() did not return an array");
        $this->assertFalse(empty($result), "fieldToDb() returned an empty array");

        $fieldName = $this->field . '_' . 'amount';
        $this->assertTrue(array_key_exists($fieldName, $result), "fieldToDb() did not return field key");
        $this->assertTrue(is_object($result[$fieldName]), "fieldToDb() did not return object value for field key");
        $this->assertTrue(is_a($result[$fieldName], 'CsvMigrations\FieldHandlers\DbField'), "fieldToDb() did not return DbField instance for field key");

        $this->assertEquals(DecimalFieldHandler::DB_FIELD_TYPE, $result[$fieldName]->getType(), "fieldToDb() did not return correct type for DbField instance");
        $this->assertEquals('decimal', $result[$fieldName]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");

        $fieldName = $this->field . '_' . 'currency';
        $this->assertTrue(array_key_exists($fieldName, $result), "fieldToDb() did not return field key");
        $this->assertTrue(is_object($result[$fieldName]), "fieldToDb() did not return object value for field key");
        $this->assertTrue(is_a($result[$fieldName], 'CsvMigrations\FieldHandlers\DbField'), "fieldToDb() did not return DbField instance for field key");

        $this->assertEquals(ListFieldHandler::DB_FIELD_TYPE, $result[$fieldName]->getType(), "fieldToDb() did not return correct type for DbField instance");
        $this->assertEquals('string', $result[$fieldName]->getType(), "fieldToDb() did not return correct hardcoded type for DbField instance");
        $this->assertEquals(255, $result[$fieldName]->getLimit(), "fieldToDb() did not return correct limit for DbField instance");
    }

    public function testGetSearchOptions()
    {
        $result = $this->fh->getSearchOptions();
        $this->assertTrue(is_array($result), "getSearchOptions() did not return an array");
        $this->assertFalse(empty($result), "getSearchOptions() returned an empty result");
    }
}

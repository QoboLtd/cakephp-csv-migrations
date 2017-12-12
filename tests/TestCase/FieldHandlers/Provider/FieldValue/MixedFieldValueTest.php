<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\FieldValue;

use Cake\Network\Request;
use Cake\ORM\Entity;
use CsvMigrations\FieldHandlers\Provider\FieldValue\FieldValueInterface;
use CsvMigrations\FieldHandlers\Provider\FieldValue\MixedFieldValue;
use PHPUnit_Framework_TestCase;

class MixedFieldValueTest extends PHPUnit_Framework_TestCase
{
    public function testProvide()
    {
        $provider = new MixedFieldValue();

        $field = 'foobar';
        $data = 'here goes some string';
        $result = $provider->provide($data, $field);
        $this->assertEquals($data, $result, "Data provider did not return data as is for string");

        $data = new Request();
        $result = $provider->provide($data, $field);
        $this->assertEquals(null, $result, "Data provider did not return null from Request");
        $data->data($field, 'hello');
        $result = $provider->provide($data, $field);
        $this->assertEquals('hello', $result, "Data provider did not return correct data from Request");

        $data = new Entity();
        $result = $provider->provide($data, $field);
        $this->assertEquals(null, $result, "Data provider did not return null from Entity");
        $data->$field = 'blah';
        $result = $provider->provide($data, $field);
        $this->assertEquals('blah', $result, "Data provider did not return correct data from Entity");
    }

    public function testConstruct()
    {
        $provider = new MixedFieldValue();
        $this->assertTrue(is_object($provider), "Failed to instantiate MixedFieldValue object");
        $this->assertTrue($provider instanceof FieldValueInterface, "MixedFieldValue instance does not implement FieldValueInterface");
    }
}

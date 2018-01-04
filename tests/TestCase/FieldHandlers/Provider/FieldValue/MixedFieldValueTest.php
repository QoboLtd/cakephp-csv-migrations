<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\FieldValue;

use Cake\Network\Request;
use Cake\ORM\Entity;
use CsvMigrations\FieldHandlers\Config\StringConfig;
use CsvMigrations\FieldHandlers\Provider\FieldValue\MixedFieldValue;
use CsvMigrations\FieldHandlers\Provider\ProviderInterface;
use PHPUnit_Framework_TestCase;

class MixedFieldValueTest extends PHPUnit_Framework_TestCase
{
    public function testProvide()
    {
        $field = 'foobar';
        $config = new StringConfig($field);
        $provider = new MixedFieldValue($config);

        $data = 'here goes some string';
        $result = $provider->provide($data);
        $this->assertEquals($data, $result, "Data provider did not return data as is for string");

        $data = new Request();
        $result = $provider->provide($data);
        $this->assertEquals(null, $result, "Data provider did not return null from Request");
        $data->data($field, 'hello');
        $result = $provider->provide($data);
        $this->assertEquals('hello', $result, "Data provider did not return correct data from Request");

        $data = new Entity();
        $result = $provider->provide($data);
        $this->assertEquals(null, $result, "Data provider did not return null from Entity");
        $data->$field = 'blah';
        $result = $provider->provide($data);
        $this->assertEquals('blah', $result, "Data provider did not return correct data from Entity");
    }

    public function testConstruct()
    {
        $field = 'foobar';
        $config = new StringConfig($field);
        $provider = new MixedFieldValue($config);
        $this->assertTrue(is_object($provider), "Failed to instantiate MixedFieldValue object");
        $this->assertTrue($provider instanceof ProviderInterface, "MixedFieldValue instance does not implement ProviderInterface");
    }
}

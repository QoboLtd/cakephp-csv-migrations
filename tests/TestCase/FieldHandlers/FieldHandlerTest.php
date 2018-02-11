<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use CsvMigrations\FieldHandlers\Config\ConfigInterface;
use CsvMigrations\FieldHandlers\FieldHandler;
use CsvMigrations\FieldHandlers\FieldHandlerInterface;
use PHPUnit_Framework_TestCase;

class FieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $table = 'fields';
    protected $field = 'field_string';
    protected $type = 'string';

    protected $fh;

    protected function setUp()
    {
        $config = ConfigFactory::getByType($this->type, $this->field, $this->table);
        $this->fh = new FieldHandler($config);
    }

    public function testInterface()
    {
        $this->assertFalse(empty($this->fh), "FieldHandler is empty");
        $this->assertTrue($this->fh instanceof FieldHandlerInterface, "FieldHandler does not implement FieldHandlerInterface");
    }

    public function testGetConfig()
    {
        $result = $this->fh->getConfig();

        $this->assertInstanceOf(ConfigInterface::class, $result);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderValueMissingRendererException()
    {
        $result = $this->fh->renderValue('test', ['renderAs' => 'thisRendererDoesNotExist']);
    }
}

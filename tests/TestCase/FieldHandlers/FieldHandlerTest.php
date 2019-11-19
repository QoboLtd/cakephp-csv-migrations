<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use CsvMigrations\FieldHandlers\Config\ConfigInterface;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandler;
use CsvMigrations\FieldHandlers\FieldHandlerInterface;
use PHPUnit\Framework\TestCase;

class FieldHandlerTest extends TestCase
{
    protected $table = 'fields';
    protected $field = 'field_string';
    protected $type = 'string';

    protected $fh;

    protected function setUp(): void
    {
        $config = ConfigFactory::getByType($this->type, $this->field, $this->table);
        $this->fh = new FieldHandler($config);
    }

    public function testInterface(): void
    {
        $this->assertFalse(empty($this->fh), "FieldHandler is empty");
        $this->assertTrue($this->fh instanceof FieldHandlerInterface, "FieldHandler does not implement FieldHandlerInterface");
    }

    public function testGetConfig(): void
    {
        $result = $this->fh->getConfig();

        $this->assertInstanceOf(ConfigInterface::class, $result);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderValueMissingRendererException(): void
    {
        $result = $this->fh->renderValue('test', ['renderAs' => 'thisRendererDoesNotExist']);
    }

    public function testGetSearchOptions(): void
    {
        $result = $this->fh->getSearchOptions();
        $this->assertTrue(is_array($result), "getSearchOptions() returned a non-array result");
        $this->assertFalse(empty($result), "getSearchOptions() returned an empty result");

        $fieldDefinitions = [
            CsvField::FIELD_NAME => 'test_field',
            CsvField::FIELD_TYPE => 'string',
            CsvField::FIELD_NON_SEARCHABLE => true,
        ];
        $result = $this->fh->getSearchOptions(['fieldDefinitions' => $fieldDefinitions]);
        $this->assertTrue(is_array($result), "getSearchOptions() returned a non-array result");
        $this->assertTrue(empty($result), "getSearchOptions() returned a non-empty result");
    }
}

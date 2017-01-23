<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\RelatedFieldHandler;
use PHPUnit_Framework_TestCase;

class RelatedFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $fh;

    protected function setUp()
    {
        $this->fh = new RelatedFieldHandler('fields', 'field_related');
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->fh));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\FieldHandlerInterface', $implementedInterfaces), "FieldHandlerInterface is not implemented");
    }

    public function testGetSearchOperators()
    {
        $result = $this->fh->getSearchOperators();
        $this->assertTrue(is_array($result), "getSearchOperators() did not return an array");
        $this->assertFalse(empty($result), "getSearchOperators() returned an empty result");
        $this->assertArrayHasKey('is', $result, "getSearchOperators() did not return 'is' key");
        $this->assertArrayHasKey('is_not', $result, "getSearchOperators() did not return 'is_not' key");
    }
}

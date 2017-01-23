<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use CsvMigrations\FieldHandlers\MetricFieldHandler;
use PHPUnit_Framework_TestCase;

class MetricFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $fh;

    protected function setUp()
    {
        $this->fh = new MetricFieldHandler('fields', 'field_metric');
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
    }
}

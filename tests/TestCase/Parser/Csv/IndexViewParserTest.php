<?php
namespace CsvMigrations\Test\TestCase\Parser\Csv;

use CsvMigrations\Parser\Csv\IndexViewParser;
use PHPUnit_Framework_TestCase;

class IndexViewParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseFromPath()
    {
        $file = dirname(dirname(dirname(__DIR__))) . DS . 'data' . DS . 'CsvMigrations' . DS . 'views' . DS . 'Foo' . DS . 'index.csv';
        $parser = new IndexViewParser();
        $result = $parser->parseFromPath($file);

        $this->assertTrue(is_array($result), "Parser returned a non-array");
        $this->assertFalse(empty($result), "Parser returned empty result");
    }
}

<?php
namespace CsvMigrations\Test\TestCase\Parser\Csv;

use CsvMigrations\Parser\Csv\DetailViewParser;
use PHPUnit_Framework_TestCase;

class DetailViewParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseFromPath()
    {
        $file = dirname(dirname(dirname(__DIR__))) . DS . 'data' . DS . 'CsvMigrations' . DS . 'views' . DS . 'Foo' . DS . 'view.csv';
        $parser = new DetailViewParser();
        $result = $parser->parseFromPath($file);

        $this->assertTrue(is_array($result), "Parser returned a non-array");
        $this->assertFalse(empty($result), "Parser returned empty result");
    }
}

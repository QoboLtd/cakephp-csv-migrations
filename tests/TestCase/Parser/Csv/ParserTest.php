<?php
namespace CsvMigrations\Test\TestCase\Parser\Csv;

use CsvMigrations\Parser\Csv\Parser;
use PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseFromPathException()
    {
        $parser = new Parser();
        $result = $parser->parseFromPath('some-non-existing-file');
    }

    public function testParseFromPath()
    {
        $file = dirname(dirname(dirname(__DIR__))) . DS . 'data' . DS . 'CsvMigrations' . DS . 'migrations' . DS . 'Foo' . DS . 'migration.dist.csv';
        $parser = new Parser();
        $result = $parser->parseFromPath($file);

        $this->assertTrue(is_array($result), "Parser returned a non-array");
        $this->assertFalse(empty($result), "Parser returned empty result");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetheadersFromPathException()
    {
        $parser = new Parser();
        $result = $parser->getHeadersFromPath('some-non-existing-file');
    }

    public function testGetHeadersFromPath()
    {
        $file = dirname(dirname(dirname(__DIR__))) . DS . 'data' . DS . 'CsvMigrations' . DS . 'migrations' . DS . 'Foo' . DS . 'migration.dist.csv';
        $parser = new Parser();
        $result = $parser->getHeadersFromPath($file);

        $this->assertTrue(is_array($result), "Parser returned a non-array for headers");
        $this->assertFalse(empty($result), "Parser returned empty result for headers");
    }
}

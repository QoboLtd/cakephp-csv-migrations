<?php
namespace CsvMigrations\Parser;

interface ParserInterface
{
    /**
     * Parse from path
     *
     * Parses a given file according to the specified options
     *
     * @param string $path    Path to file
     * @param array  $options Options for parsing
     * @return array
     */
    public function parseFromPath($path, array $options = []);
}

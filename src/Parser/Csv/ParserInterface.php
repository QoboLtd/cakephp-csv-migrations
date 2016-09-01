<?php
namespace CsvMigrations\Parser\Csv;

interface ParserInterface
{
    /**
     * Parse from path
     *
     * Parses a given file according to the specified structure
     *
     * @param string $path      Path to file
     * @param array  $structure Structure of the file
     * @return array
     */
    public function parseFromPath($path, array $structure = []);
}

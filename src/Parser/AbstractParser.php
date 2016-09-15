<?php
namespace CsvMigrations\Parser;

abstract class AbstractParser implements ParserInterface
{
    /**
     * Parsing options
     */
    protected $options = [];

    /**
     * Parse from path
     *
     * Parses a given file according to the specified options
     *
     * @param string $path    Path to file
     * @param array  $options Options for parsing
     * @return array
     */
    abstract public function parseFromPath($path, array $options = []);

    /**
     * Validate path
     *
     * @throws \InvalidArgumentException If $path does not exist or is not readable
     * @param string $path Path to validate
     * @return void
     */
    protected function validatePath($path)
    {
        if (!is_readable($path)) {
            throw new \InvalidArgumentException("Path does not exist or is not readable: $path");
        }
    }
}

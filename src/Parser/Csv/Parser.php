<?php
namespace CsvMigrations\Parser\Csv;

use League\Csv\Reader;

/**
 * Generic CSV Parser
 *
 * This parser is useful for generic CSV processing.
 * You can either provide the expected structure, or
 * it will fallback on the headers from the first row.
 *
 * It is assumed that the first row ALWAYS contains
 * the column headers.
 *
 * @author Leonid Mamchenkov <l.mamchenkov@qobo.biz>
 */
class Parser implements ParserInterface
{
    /**
     * File structure
     */
    protected $structure = [];

    /**
     * Parse from path
     *
     * Parses a given file according to the specified structure
     *
     * @param string $path      Path to file
     * @param array  $structure Structure of the of the file
     * @return array
     */
    public function parseFromPath($path, array $structure = [])
    {
        $result = [];

        $this->validatePath($path);

        // Overwrite defaults
        if (!empty($structure)) {
            $this->structure = $structure;
        }

        // If no structure specified (default or param), then use headers
        if (empty($this->structure)) {
            $this->structure = $this->getHeadersFromPath($path);
        }

        $reader = Reader::createFromPath($path);
        $rows = $reader->setOffset(1)->fetchAssoc($this->structure);
        foreach ($rows as $row) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Get headers from path
     *
     * @param string $path Path to file
     * @return array
     */
    public function getHeadersFromPath($path)
    {
        $result = [];

        $this->validatePath($path);

        $reader = Reader::createFromPath($path);
        $result = $reader->fetchOne();

        return $result;
    }

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

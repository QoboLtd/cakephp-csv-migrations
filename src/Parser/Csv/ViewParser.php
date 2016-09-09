<?php
namespace CsvMigrations\Parser\Csv;

use League\Csv\Reader;

/**
 * View CSV Parser
 *
 * This parser is useful for view CSV processing.
 * View CSV files can vary in their format, depending
 * on the need of the view (for example, we currently
 * support add/edit/view with panels and index view,
 * which is completely different.
 *
 * Therefor, it is assumed that the caller of the
 * parser knows how to best handle the returned results.
 *
 * It is assumed that the first row ALWAYS contains
 * the column headers.
 *
 * @author Leonid Mamchenkov <l.mamchenkov@qobo.biz>
 */
class ViewParser implements ParserInterface
{
    /**
     * Mode to use for opening CSV files
     */
    protected $open_mode = 'r';

    /**
     * Parse from path
     *
     * Parses a given file and return a list of all
     * records.
     *
     * $structure argument is here for the compatibility
     * with the interface.  It does not affect the return
     * of the parsing.
     *
     * @param string $path      Path to file
     * @param array  $structure Structure of the file
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

        $reader = Reader::createFromPath($path, $this->open_mode);
        $result = $reader->setOffset(1)->fetchAll();

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

        $reader = Reader::createFromPath($path, $this->open_mode);
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

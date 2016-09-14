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
class Parser extends AbstractCsvParser
{
    /**
     * Parse from path
     *
     * Parses a given file according to the specified options
     *
     * @param string $path    Path to file
     * @param array  $options Parsing options
     * @return array
     */
    public function parseFromPath($path, array $options = [])
    {
        $result = [];

        $this->validatePath($path);

        // Overwrite defaults
        if (!empty($options)) {
            $this->options = $options;
        }

        // If no structure specified (default or param), then use headers
        if (empty($this->options['structure'])) {
            $this->options['structure'] = $this->getHeadersFromPath($path);
        }

        $reader = Reader::createFromPath($path, $this->open_mode);
        $rows = $reader->setOffset(1)->fetchAssoc($this->options['structure']);
        foreach ($rows as $row) {
            $result[] = $row;
        }

        return $result;
    }
}

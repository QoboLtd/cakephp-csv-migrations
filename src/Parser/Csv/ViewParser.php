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
class ViewParser extends AbstractCsvParser
{
    /**
     * Parse from path
     *
     * Parses a given file and return a list of all
     * records.
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
        $result = $reader->setOffset(1)->fetchAll();

        return $result;
    }
}

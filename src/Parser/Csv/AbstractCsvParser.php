<?php
namespace CsvMigrations\Parser\Csv;

use CsvMigrations\Parser\AbstractParser;
use League\Csv\Reader;

abstract class AbstractCsvParser extends AbstractParser
{
    /**
     * Mode to use for opening CSV files
     */
    protected $open_mode = 'r';

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
}

<?php
namespace CsvMigrations\Parser\Ini;

use Piwik\Ini\IniReader;

/**
 * Generic INI Parser
 *
 * This parser is useful for generic INI processing.
 *
 * @author Leonid Mamchenkov <l.mamchenkov@qobo.biz>
 */
class Parser extends AbstractIniParser
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

        $reader = new IniReader();
        $result = $reader->readFile($path);

        return $result;
    }
}

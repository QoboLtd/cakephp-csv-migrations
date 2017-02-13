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

    /**
     * Get fields ini parameters
     *
     * Retrieves and returns fields ini parameters. It accepts a single parameter
     * in a string format or multiple parameters in an array format. If no parameters
     * are requested then the full field ini configuration is returned.
     *
     * If no field ini configration was found then the return value will be an empty
     * array if multiple parameters were requested or null if one parameter was
     * requested.
     *
     * @param string $path Fields ini path
     * @param string $field Field name
     * @param string|array|null $params Field ini parameters
     * @return string|array|null
     */
    public function getFieldsIniParams($path, $field, $params = null)
    {
        if (is_array($params) && 1 === count($params)) {
            $params = current($params);
        }
        $result = is_string($params) ? null : [];

        if (!is_string($path)) {
            throw new \RuntimeException('Path must be a string');
        }

        // skip if path or field values not provided
        if (empty($path) || empty($field)) {
            return $result;
        }

        $parsed = [];
        try {
            $parsed = $this->parseFromPath($path);
        } catch (\Exception $e) {
            //
        }

        // return empty result if fields ini was not parsed
        if (empty($parsed)) {
            return $result;
        }

        // return empty result if current field has no ini configuration
        if (empty($parsed[$field])) {
            return $result;
        }

        // return all configuration for current field if no specific params requested
        if (empty($params)) {
            return $parsed[$field];
        }

        if (is_string($params)) {
            $result = !empty($parsed[$field][$params]) ? $parsed[$field][$params] : $result;

            return $result;
        }

        // pick only requested params for current field ini configuration
        foreach ($params as $param) {
            if (empty($parsed[$field][$param])) {
                continue;
            }

            $result[$param] = $parsed[$field][$param];
        }

        return $result;
    }
}

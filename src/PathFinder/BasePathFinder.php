<?php
namespace CsvMigrations\PathFinder;

use Cake\Core\Configure;

abstract class BasePathFinder implements PathFinderInterface
{
    protected $pathConfigKey = 'CsvMigrations.modules.path';
    protected $prefix;
    protected $fileName;

    /**
     * Find path
     *
     * @throws \InvalidArgumentException when module or path is not specified
     * @param string $module Module to look for files in
     * @param string $path     Path to look for
     * @return null|string|array Null for not found, string for single path, array for multiple paths
     */
    public function find($module, $path = null)
    {
        if (empty($module)) {
            throw new \InvalidArgumentException("Module is not specified");
        }
        if (!is_string($module)) {
            throw new \InvalidArgumentException("Module name is not a string");
        }

        if (empty($path)) {
            $path = $this->fileName;
        }
        if (!is_string($path)) {
            throw new \InvalidArgumentException("Path is not a string");
        }

        if (empty($this->pathConfigKey)) {
            throw new \InvalidArgumentException("pathConfigKey is empty");
        }
        $result = Configure::readOrFail($this->pathConfigKey);
        $result .= $module . DIRECTORY_SEPARATOR;

        if (!empty($this->prefix)) {
            $path = $this->prefix . DIRECTORY_SEPARATOR . $path;
        }

        $result .= $path;

        $this->validatePath($result);

        return $result;
    }

    /**
     * Check validity of the given path
     *
     * If the path is not valid, throw an exception.
     *
     * @throws \InvalidArgumentException when path is invalid
     * @param string $path Path to validate
     * @return void
     */
    public function validatePath($path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Path does not exist [$path]");
        }
        if (!is_readable($path)) {
            throw new \InvalidArgumentException("Path is not readable [$path]");
        }
    }
}

<?php
namespace CsvMigrations\PathFinder;

use Cake\Core\Configure;

abstract class BasePathFinder implements PathFinderInterface
{
    protected $requireModule = true;
    protected $pathConfigKey = 'CsvMigrations.modules.path';
    protected $prefix;
    protected $fileName;

    /**
     * Find path
     *
     * Most files will require the $module parameter to
     * make search more specific.
     *
     * @param string $module Module to look for files in
     * @param string $path     Path to look for
     * @return null|string|array Null for not found, string for single path, array for multiple paths
     */
    public function find($module = null, $path = null)
    {
        if ($this->requireModule) {
            if (empty($module)) {
                throw new \InvalidArgumentException("Module is not specified");
            }
            if (!is_string($module)) {
                throw new \InvalidArgumentException("Module name is not a string");
            }
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
        if ($this->requireModule) {
            $result .= $module . DIRECTORY_SEPARATOR;
        }

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
     * @throws \InvalidArgumentException
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

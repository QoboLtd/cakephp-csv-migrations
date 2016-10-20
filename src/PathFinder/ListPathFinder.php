<?php
namespace CsvMigrations\PathFinder;

use Cake\Core\Configure;

/**
 * ListPathFinder Class
 *
 * This path finder is here to assist with finding
 * the paths to the list files.  $path
 * parameter is required, but $module is optional.
 *
 * @author Leonid Mamchenkov <l.mamchenkov@qobo.biz>
 */
class ListPathFinder extends BasePathFinder
{
    protected $requireModule = false;
    protected $pathConfigKey = 'CsvMigrations.lists.path';
    protected $extension = '.csv';

    /**
     * Find path
     *
     * Find path to a given liste.  Make sure that $path
     * parameter is required, and, if the value is
     * given without the file extension, attach one to make it
     * easier to find.  Also, lists are currently NOT under
     * modules, so the $module parameter is optional.
     *
     * @param string $module Module to look for files in
     * @param string $path     Path to look for
     * @return null|string|array Null for not found, string for single path, array for multiple paths
     */
    public function find($module = null, $path = null)
    {
        if (empty($path)) {
            throw new \InvalidArgumentException("Path is not specified");
        }
        if (!is_string($path)) {
            throw new \InvalidArgumentException("Path is not a string");
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (empty($extension)) {
            $path .= $this->extension;
        }

        return parent::find($module, $path);
    }
}

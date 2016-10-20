<?php
namespace CsvMigrations\PathFinder;

use Cake\Core\Configure;

/**
 * ViewPathFinder Class
 *
 * This path finder is here to assist with finding
 * the paths to the module view files.  $path
 * parameter is required.
 *
 * @author Leonid Mamchenkov <l.mamchenkov@qobo.biz>
 */
class ViewPathFinder extends BasePathFinder
{
    protected $pathConfigKey = 'CsvMigrations.views.path';
    protected $extension = '.csv';

    /**
     * Find path
     *
     * Find path to a given view of a given module.  Make sure
     * that $path parameter is required, and, if the value is
     * given without the file extension, attach one to make it
     * easier to use with contoller actions and the like.
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

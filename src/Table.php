<?php
namespace CsvAssociations;

use Cake\Core\Configure;
use Cake\ORM\Table as BaseTable;
use Cake\Utility\Inflector;

/**
 * Accounts Model
 *
 */
class Table extends BaseTable
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->_setAssociationsFromCsv($config);
    }

    /**
     * Method that sets current model table associations.
     * @param array $config The configuration for the Table.
     * @return void
     */
    protected function _setAssociationsFromCsv(array $config)
    {
        $path = Configure::readOrFail('CsvAssociations.path');
        $csvFiles = $this->_getCsvFiles($path);
        $csvData = $this->_getCsvData($csvFiles);

        if (!empty($csvData)) {
            foreach ($csvData as $module => $fields) {
                foreach ($fields as $row) {
                    if (1 < count($row)) {
                        if (false !== ($pos = strpos($row['1'], ':'))) {
                            $associatedModule = substr($row['1'], $pos + 1);
                            $associatedModule = Inflector::camelize($associatedModule);
                            if ($config['alias'] === $module) {
                                $this->belongsTo($associatedModule, ['foreignKey' => $row[0]]);
                            } elseif ($config['alias'] === $associatedModule) {
                                $this->hasMany($module);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Method that retrieves csv file path(s) from specified directory recursively.
     * @param  string $path directory to search in.
     * @return array        csv file paths grouped by parent directory.
     */
    protected function _getCsvFiles($path)
    {
        $result = [];
        $dir = new \DirectoryIterator($path);
        foreach ($dir as $it) {
            if ($it->isDir() && !$it->isDot()) {
                $subDir = new \DirectoryIterator($it->getPathname());
                foreach ($subDir as $fileInfo) {
                    if ($fileInfo->isFile() && 'csv' === $fileInfo->getExtension()) {
                        $result[$it->getFilename()][] = $fileInfo->getPathname();
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Method that retrieves csv file data.
     * @param  array $csvFiles csv file path(s)
     * @return array           csv data
     */
    protected function _getCsvData(array $csvFiles)
    {
        $result = [];
        foreach ($csvFiles as $module => $paths) {
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    if (false !== ($handle = fopen($path, 'r'))) {
                        while (false !== ($data = fgetcsv($handle, 0, ','))) {
                            $result[$module][] = $data;
                        }
                        fclose($handle);
                    }
                }
            }
        }

        return $result;
    }
}

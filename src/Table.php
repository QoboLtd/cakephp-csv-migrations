<?php
namespace CsvMigrations;

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
                        $assocModule = $this->_getAssociatedModuleName($row[1]);
                        if (!empty($assocModule)) {
                            if ($config['alias'] === $module) {
                                $assocName = $this->_createAssociationName($assocModule, $row[0]);
                                $this->belongsTo($assocName, [
                                    'className' => $assocModule,
                                    'foreignKey' => $row[0]
                                ]);
                            } elseif ($config['alias'] === $assocModule) {
                                $assocName = $this->_createAssociationName($module, $row[0]);
                                $this->hasMany($assocName, [
                                    'className' => $module,
                                    'foreignKey' => $row[0]
                                ]);
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
        if (file_exists($path)) {
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

    /**
     * Method that extracts module name from field type definition.
     * @param  string $name field type
     * @return string
     */
    protected function _getAssociatedModuleName($name)
    {
        $result = '';
        $pattern = 'related:';
        if (false !== $pos = strpos($name, $pattern)) {
            $result = str_replace($pattern, '', $name);
        }

        return $result;
    }

    /**
     * Method that generates association naming based on passed parameters.
     * @param  string $module     module name
     * @param  string $foreignKey foreign key name
     * @return string
     */
    protected function _createAssociationName($module, $foreignKey = '')
    {
        if ('' !== $foreignKey) {
            $foreignKey = Inflector::camelize($foreignKey);
        }
        return $foreignKey . $module;
    }
}

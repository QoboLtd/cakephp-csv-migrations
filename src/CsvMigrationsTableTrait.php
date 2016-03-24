<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use Cake\Utility\Inflector;

trait CsvMigrationsTableTrait
{
    protected $_fieldParams = ['name', 'type', 'limit', 'required'];

    /**
     * Method that retrieves fields from csv file and returns them in associate array format.
     * @return array
     */
    public function getFieldsDefinitions()
    {
        $path = Configure::readOrFail('CsvAssociations.path') . $this->alias() . DS;

        $csvFiles = $this->_getCsvFile($path);
        $csvData = $this->_getCsvData($csvFiles);

        $result = [];
        if (!empty($csvData)) {
            foreach ($csvData as $module => $fields) {
                foreach ($fields as $row) {
                    $field = array_combine($this->_fieldParams, $row);
                    $result[$field['name']] = $field;
                }
            }
        }

        return $result;
    }

    /**
     * Method that retrieves csv file path from specified directory.
     * @param  string $path directory to search in
     * @return array        csv file path
     */
    protected function _getCsvFile($path)
    {
        $result = [];
        if (file_exists($path)) {
            $di = new \DirectoryIterator($path);
            foreach ($di as $fileInfo) {
                if ($fileInfo->isFile() && 'csv' === $fileInfo->getExtension()) {
                    $result[$this->alias()][] = $fileInfo->getPathname();
                }
            }
        }

        return $result;
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
            return;
        }

        foreach ($csvData as $module => $fields) {
            foreach ($fields as $row) {
                /*
                Skip if row is incomplete
                 */
                if (1 >= count($row)) {
                    continue;
                }

                $assocModule = $this->_getAssociatedModuleName($row[1]);
                /*
                Skip if not associated module name was found
                 */
                if (empty($assocModule)) {
                    continue;
                }

                /*
                If current model alias matches csv module, then assume belongsTo association.
                Else if it matches associated module, then assume hasMany association.
                 */
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
            $result = Inflector::camelize($result);
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

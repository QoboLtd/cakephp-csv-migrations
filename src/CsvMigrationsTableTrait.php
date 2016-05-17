<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use CsvMigrations\CsvMigrationsUtils;

trait CsvMigrationsTableTrait
{
    /**
     * Field parameters
     *
     * @var array
     */
    protected $_fieldParams = ['name', 'type', 'limit', 'required', 'non-searchable'];

    /**
     * Method that retrieves fields from csv file and returns them in associate array format.
     *
     * @return array
     */
    public function getFieldsDefinitions()
    {
        $path = Configure::readOrFail('CsvMigrations.migrations.path') . $this->alias() . DS;

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
     *
     * @param  string $path directory to search in
     * @return array        csv file path
     */
    protected function _getCsvFile($path)
    {
        $result = [];
        $fileName = Configure::readOrFail('CsvMigrations.migrations.filename');
        if (file_exists($path)) {
            $di = new \DirectoryIterator($path);
            foreach ($di as $fileInfo) {
                if ($fileInfo->isFile() && $fileName . '.csv' === $fileInfo->getFilename()) {
                    $result[$this->alias()][] = $fileInfo->getPathname();
                }
            }
        }

        return $result;
    }

    /**
     * Method that sets current model table associations.
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    protected function _setAssociations(array $config)
    {
        $this->_setAssociationsFromCsv($config);
        $this->_setAssociationsFromConfig($config);
    }

    /**
     * Method that sets current model table associations from config file.
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    protected function _setAssociationsFromConfig(array $config)
    {
        if (empty($this->_config['manyToMany']['modules'])) {
            return;
        }

        $manyToMany = explode(',', $this->_config['manyToMany']['modules']);

        foreach($manyToMany as $module) {
            $this->belongsToMany($module);
        }
    }

    /**
     * Method that sets current model table associations from csv file.
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    protected function _setAssociationsFromCsv(array $config)
    {
        $path = Configure::readOrFail('CsvMigrations.migrations.path');
        $csvFiles = $this->_getCsvFiles($path);
        $csvData = $this->_getCsvData($csvFiles);

        if (empty($csvData)) {
            return;
        }

        foreach ($csvData as $module => $fields) {
            foreach ($fields as $row) {
                $assocModule = $this->_getAssociatedModuleName($row[1]);
                /*
                Skip if not associated module name was found
                 */
                if ('' === trim($assocModule)) {
                    continue;
                }

                /*
                If current model alias matches csv module, then assume belongsTo association.
                Else if it matches associated module, then assume hasMany association.
                 */
                if ($config['alias'] === $module) {
                    $assocName = CsvMigrationsUtils::createAssociationName($assocModule, $row[0]);
                    $this->belongsTo($assocName, [
                        'className' => $assocModule,
                        'foreignKey' => $row[0]
                    ]);
                } elseif ($config['registryAlias'] === $assocModule) {
                    $assocName = CsvMigrationsUtils::createAssociationName($module, $row[0]);
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
     *
     * @param  string $path directory to search in.
     * @return array        csv file paths grouped by parent directory.
     */
    protected function _getCsvFiles($path)
    {
        $result = [];
        $filename = Configure::read('CsvMigrations.migrations.filename') . '.csv';
        if (file_exists($path)) {
            $dir = new \DirectoryIterator($path);
            foreach ($dir as $it) {
                if ($it->isDir() && !$it->isDot()) {
                    $subDir = new \DirectoryIterator($it->getPathname());
                    foreach ($subDir as $fileInfo) {
                        if ($fileInfo->isFile() && $filename === $fileInfo->getFilename()) {
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
     *
     * @param  array $csvFiles csv file path(s)
     * @return array           csv data
     */
    protected function _getCsvData(array $csvFiles)
    {
        $result = [];
        $count = count($this->_fieldParams);
        foreach ($csvFiles as $module => $paths) {
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    if (false !== ($handle = fopen($path, 'r'))) {
                        $row = 0;
                        while (false !== ($data = fgetcsv($handle, 0, ','))) {
                            // skip first row
                            if (0 === $row) {
                                $row++;
                                continue;
                            }
                            /*
                            Skip if row is incomplete
                             */
                            if ($count !== count($data)) {
                                continue;
                            }

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
     *
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
}

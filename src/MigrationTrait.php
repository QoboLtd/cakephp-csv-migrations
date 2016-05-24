<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use CsvMigrations\CsvMigrationsUtils;
use CsvMigrations\CsvTrait;

trait MigrationTrait
{
    use CsvTrait;

    /**
     * Pattern for associated fields
     *
     * @var string
     */
    protected $_patternAssoc = 'related:';

    /**
     * Field parameters
     *
     * @var array
     */
    protected $_defaultParams = [
        'name' => '',
        'type' => '',
        'limit' => '',
        'required' => '',
        'non-searchable' => ''
    ];

    /**
     * Method that retrieves fields from csv file and returns them in associate array format.
     *
     * @return array
     */
    public function getFieldsDefinitions()
    {
        $path = Configure::readOrFail('CsvMigrations.migrations.path') . $this->alias() . DS;

        $csvFiles = $this->_getCsvFile($path);

        $csvData = [];
        foreach ($csvFiles as $module => $paths) {
            foreach ($paths as $path) {
                $csvData[$module] = $this->_getCsvData($path);
            }
        }

        $result = [];
        if (!empty($csvData)) {
            foreach ($csvData as $module => $fields) {
                foreach ($fields as $row) {
                    $field = array_combine(array_keys($this->_defaultParams), $row);
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

        $csvData = [];
        foreach ($csvFiles as $module => $paths) {
            foreach ($paths as $path) {
                $csvData[$module] = $this->_prepareCsvData(
                    $this->_getCsvData($path)
                );
            }
        }

        if (empty($csvData)) {
            return;
        }

        foreach ($csvData as $module => $fields) {
            foreach ($fields as $row) {
                $assocModule = $this->_getAssociatedModuleName($row['type']);
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
                    $assocName = CsvMigrationsUtils::createAssociationName($assocModule, $row['name']);
                    $this->belongsTo($assocName, [
                        'className' => $assocModule,
                        'foreignKey' => $row['name']
                    ]);
                } elseif ($config['registryAlias'] === $assocModule) {
                    $assocName = CsvMigrationsUtils::createAssociationName($module, $row['name']);
                    $this->hasMany($assocName, [
                        'className' => $module,
                        'foreignKey' => $row['name']
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
     * Method that extracts module name from field type definition.
     *
     * @param  string $name field type
     * @return string
     */
    protected function _getAssociatedModuleName($name)
    {
        $result = '';
        if (false !== $pos = strpos($name, $this->_patternAssoc)) {
            $result = str_replace($this->_patternAssoc, '', $name);
            $result = Inflector::camelize($result);
        }

        return $result;
    }
}

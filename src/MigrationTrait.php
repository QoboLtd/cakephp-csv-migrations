<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use CsvMigrations\CsvMigrationsUtils;
use CsvMigrations\CsvTrait;
use CsvMigrations\FieldHandlers\CsvField;
use RuntimeException;

trait MigrationTrait
{
    use CsvTrait;

    /**
     * File extension
     */
    private $__extension = 'csv';

    /**
     * Associated fields identifier
     *
     * @var string
     */
    private $__assocIdentifier = 'related';

    /**
     * Method that retrieves fields from csv file and returns them in associate array format.
     *
     * @param  string $moduleName Module Name
     * @return array
     */
    public function getFieldsDefinitions($moduleName = null)
    {
        if (is_null($moduleName)) {
            if (is_callable([$this, 'alias'])) {
                $moduleName = $this->alias();
            } else {
                throw new RuntimeException("Failed getting field definitions for unknown module");
            }
        }

        $path = Configure::readOrFail('CsvMigrations.migrations.path') . $moduleName . DS;
        $path .= Configure::readOrFail('CsvMigrations.migrations.filename') . '.' . $this->__extension;

        $result = $this->_prepareCsvData($this->_getCsvData($path));

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

        foreach ($manyToMany as $module) {
            $this->belongsToMany($module, [
                'className' => $module
            ]);
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
        $csvData = $this->_csvData(true);
        $csvObjData = $this->_csvDataToCsvObj($csvData);
        $csvFilteredData = $this->_csvDataFilter($csvObjData, $this->__assocIdentifier);

        foreach ($csvFilteredData as $csvModule => $fields) {
            foreach ($fields as $csvObjField) {
                $assoccsvModule = $csvObjField->getAssocCsvModule();
                $fieldName = $csvObjField->getName();

                if ($config['table'] === $csvModule) {
                    $assocName = CsvMigrationsUtils::createAssociationName($assoccsvModule, $fieldName);
                    //Belongs to association of the curren running module.
                    $this->belongsTo($assocName, [
                        'className' => $assoccsvModule,
                        'foreignKey' => $fieldName
                    ]);
                } elseif ($config['table'] === $assoccsvModule) {
                    //Foreignkey found in other module
                    $assocName = CsvMigrationsUtils::createAssociationName($csvModule, $fieldName);
                    $this->hasMany($assocName, [
                        'className' => $csvModule,
                        'foreignKey' => $fieldName
                    ]);
                }


            }
        }
    }

    /**
     * Filter the CSV data by type.
     *
     * @param  array  $data CSV data.
     * @param  string $type Type to filter.
     * @return array  Filtered data.
     */
    protected function _csvDataFilter(array $data = [], $type = null)
    {
        foreach ($data as $csvModule => &$fields) {
            foreach ($fields as $key => $field) {
                if ($field->getType() !== $type) {
                    unset($fields[$key]);
                }
            }
        }

        return $data;
    }

    /**
     * Convert field details into CSV object.
     *
     * @see  _csvData();
     * @param  array  $data The return of _csvData function
     * @return array        An array containing CSV objects.
     */
    protected function _csvDataToCsvObj(array $data = [])
    {
        foreach ($data as $csvModule => &$fields) {
            foreach ($fields as $key => $fieldDetails) {
                $fields[$key] = new CsvField($fieldDetails);
            }
        }

        return $data;
    }

    /**
     * Get all modules data.
     *
     * @return array Modules, fields and fields types.
     */
    protected function _csvData()
    {
        $result = [];
        $path = Configure::readOrFail('CsvMigrations.migrations.path');
        $csvFiles = $this->_getCsvFiles($path);
        $plugin = $this->_getPluginNameFromPath($path);

        foreach ($csvFiles as $csvModule => $paths) {
            if (!is_null($plugin)) {
                $csvModule = $plugin . '.' . $csvModule;
            }
            foreach ($paths as $path) {
                $result[$csvModule] = $this->_prepareCsvData($this->_getCsvData($path));
            }
        }

        return $result;
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
     * Returns the name of the plugin from its path.
     *
     * @param  string $path Path of the plugin.
     * @return string       Name of plugin.
     */
    protected function _getPluginNameFromPath($path)
    {
        foreach (Configure::read('plugins') as $name => $pluginPath) {
            $pos = strpos($path, $pluginPath);
            if ($pos !== false) {
                return $name;
            }
        }
        return null;
    }
}

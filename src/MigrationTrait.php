<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use CsvMigrations\CsvMigrationsUtils;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\Parser\Csv\MigrationParser;
use CsvMigrations\Parser\Ini\Parser;
use CsvMigrations\PathFinder\MigrationPathFinder;
use RuntimeException;

trait MigrationTrait
{
    /**
     * Associated fields identifiers
     *
     * @var array
     */
    private $__assocIdentifiers = ['related', 'files'];

    /**
     * Method that retrieves fields from csv file and returns them in associate array format.
     *
     * @param  string $moduleName Module Name
     * @return array
     */
    public function getFieldsDefinitions($moduleName = null)
    {
        $result = [];

        if (is_null($moduleName)) {
            if (is_callable([$this, 'alias'])) {
                $moduleName = $this->alias();
            } else {
                throw new RuntimeException("Failed getting field definitions for unknown module");
            }
        }

        $pathFinder = new MigrationPathFinder;
        $path = $pathFinder->find($moduleName);

        // Parser knows how to make sure that the file exists.  But it can
        // also throw other exceptions, which we don't want to avoid for
        // now.
        if (is_readable($path)) {
            $parser = new MigrationParser();
            $result = $parser->wrapFromPath($path);
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
        $csvFilteredData = $this->_csvDataFilter($csvObjData, $this->__assocIdentifiers);

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
     * @param  array  $data  CSV data.
     * @param  array  $types Types to filter.
     * @return array         Filtered data.
     */
    protected function _csvDataFilter(array $data = [], array $types = [])
    {
        if (empty($data)) {
            return $data;
        }

        foreach ($data as $csvModule => &$fields) {
            foreach ($fields as $key => $field) {
                if (!in_array($field->getType(), $types)) {
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
        /*
        covers case where CsvMigration configuration files reside in a plugin.
         */
        $plugin = $this->_getPluginNameFromPath($path);

        if (is_null($plugin)) {
            /*
            covers case where CsvMigration model and controller reside in
            a plugin (even if configuration files reside in the APP level).
             */
            $plugin = $this->_getPluginNameFromRegistryAlias();
        }

        $parser = new MigrationParser();
        foreach ($csvFiles as $csvModule => $paths) {
            if (!is_null($plugin)) {
                $csvModule = $plugin . '.' . $csvModule;
            }
            foreach ($paths as $path) {
                $result[$csvModule] = $parser->wrapFromPath($path);
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
     * Used in <model>/report/<slug> method
     * to get reports from the ini file on the dynamic
     * model/table.
     * @return array $config containing all reports from ini files
     */
    public function _getReports()
    {
        $result = $config = [];

        $filename = Configure::read('CsvMigrations.reports.filename');
        $filename .= '.ini';

        $path = Configure::read('CsvMigrations.migrations.path');
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

        if (!empty($result)) {
            $parser = new Parser();
            foreach ($result as $model => $paths) {
                foreach ($paths as $p) {
                    $config[$model] = $parser->parseFromPath($p);
                }
            }
        }

        return $config;
    }

    /**
     * Returns the name of the plugin from its path.
     *
     * @param  string $path Path of the plugin.
     * @return string       Name of plugin.
     */
    protected function _getPluginNameFromPath($path)
    {
        $plugins = Configure::read('plugins');

        if (is_null($plugins)) {
            return null;
        }

        foreach ($plugins as $name => $pluginPath) {
            $pos = strpos($path, $pluginPath);
            if ($pos !== false) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Returns the name of the plugin from current Table's registry alias value.
     *
     * @return string Name of plugin.
     */
    protected function _getPluginNameFromRegistryAlias()
    {
        list($plugin) = pluginSplit($this->registryAlias());

        return $plugin;
    }
}

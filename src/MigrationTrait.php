<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use CsvMigrations\CsvMigrationsUtils;
use CsvMigrations\CsvTrait;
use CsvMigrations\FieldHandlers\CsvField;

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
     * @return array
     */
    public function getFieldsDefinitions()
    {
        $path = Configure::readOrFail('CsvMigrations.migrations.path') . $this->alias() . DS;
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
        $csvData = $this->_csvData();
        $curModule = Inflector::camelize($this->table());

        foreach ($csvData as $csvModule => $fields) {
            foreach ($fields as $field) {
                $csvField = new CsvField($field);
                /*
                Skip if not associated csvModule name was found
                 */
                if ($this->__assocIdentifier !== $csvField->getType()) {
                    continue;
                }
                $assoccsvModule = $csvField->getLimit();
                /*
                If current model alias matches csv csvModule, then assume belongsTo association.
                Else if it matches associated csvModule, then assume hasMany association.
                 */
                if ($curModule === $csvModule) {
                    $assocName = CsvMigrationsUtils::createAssociationName($assoccsvModule, $field['name']);
                    $this->belongsTo($assocName, [
                        'className' => $assoccsvModule,
                        'foreignKey' => $field['name']
                    ]);
                } else {
                    list(, $mod) = pluginSplit($assoccsvModule);
                    if ($curModule === $mod) {
                        list($plugin, $controller) = pluginSplit($config['registryAlias']);
                        /**
                         * appending plugin name from current table to associated csvModule.
                         * @todo investigate more, it might break in some cases, such as Files plugin association.
                         */
                        if (!is_null($plugin)) {
                            $assoccsvModule = $plugin . '.' . $csvModule;
                        }
                        $assocName = CsvMigrationsUtils::createAssociationName($assoccsvModule, $field['name']);
                        $this->hasMany($assocName, [
                            'className' => $assoccsvModule,
                            'foreignKey' => $field['name']
                        ]);
                    }

                }


            }
        }
    }

    /**
     * [_csvData description]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    protected function _csvData()
    {
        $result = [];
        $path = Configure::readOrFail('CsvMigrations.migrations.path');
        $csvFiles = $this->_getCsvFiles($path);

        foreach ($csvFiles as $csvModule => $paths) {
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
}

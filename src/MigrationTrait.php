<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CsvMigrations;

use Cake\Core\App;
use Cake\Core\Configure;
use CsvMigrations\CsvMigrationsUtils;
use CsvMigrations\FieldHandlers\CsvField;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;

trait MigrationTrait
{
    /**
     * Cached CSV field definitions for the current module
     *
     * @var array
     */
    protected $_fieldDefinitions = [];

    /**
     * Associated fields identifiers
     *
     * @var array
     */
    private $__assocIdentifiers = ['related'];

    /**
     * Get fields from CSV file
     *
     * This method gets all fields defined in the CSV and returns
     * them as an associative array.
     *
     * Additionally, an associative array of stub fields can be
     * passed, which will be included in the returned definitions.
     * This is useful when working with fields which are NOT part
     * of the migration.csv definitions, such as combined fields
     * and virtual fields.
     *
     * If the field exists in the CSV configuration and is passed
     * as a stub field, then the CSV definition will be preferred.
     *
     * Note that this method is called very frequently during the
     * rendering of the views, so performance is important.  For
     * this reason, parsed definitions are stored in the property
     * to avoid unnecessary processing of files and conversion of
     * data. Stub fields, however, won't be cached as they are not
     * real definitions and might vary from call to call.
     *
     * There are cases, when no field definitions are available at
     * all.  For example, external, non-CSV modules.  For those
     * cases, all exceptions and errors are silenced and an empty
     * array of field definitions is returned.  Unless, of course,
     * there are stub fields provided.
     *
     * @param  array $stubFields Stub fields
     * @return array             Associative array of fields and their definitions
     */
    public function getFieldsDefinitions(array $stubFields = [])
    {
        $result = [];

        // Get cached definitions
        if (!empty($this->_fieldDefinitions)) {
            $result = $this->_fieldDefinitions;
        }

        // Fetch definitions from CSV if cache is empty
        if (empty($result)) {
            $moduleName = App::shortName(get_class($this), 'Model/Table', 'Table');
            list(, $moduleName) = pluginSplit($moduleName);

            $mc = new ModuleConfig(ConfigType::MIGRATION(), $moduleName);
            $result = (array)json_decode(json_encode($mc->parse()), true);
            if (!empty($result)) {
                $this->_fieldDefinitions = $result;
            }
        }

        if (empty($stubFields)) {
            return $result;
        }

        // Merge $result with $stubFields
        foreach ($stubFields as $field => $definition) {
            if (!array_key_exists($field, $result)) {
                $result[$field] = $definition;
            }
        }

        return $result;
    }

    /**
     * Set current model table associations
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
     * Set current model table associations from config file
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    protected function _setAssociationsFromConfig(array $config)
    {
        $mc = new ModuleConfig(ConfigType::MODULE(), $this->getRegistryAlias());
        $config = $mc->parse();
        $modules = $config->manyToMany->modules;
        if (empty($modules)) {
            return;
        }

        foreach ($modules as $module) {
            $this->belongsToMany($module, [
                'className' => $module
            ]);
        }
    }

    /**
     * Set current model table associations from CSV file
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    protected function _setAssociationsFromCsv(array $config)
    {
        $data = $this->_csvDataToCsvObj($this->_csvData());

        if (!empty($data[$config['table']])) {
            $this->setFileAssociations($data[$config['table']]);
        }

        $this->setFieldAssociations($config, $data);
    }

    /**
     * Set associations with FileStorage table.
     *
     * @param array $fields Current module csv fields.
     * @return void
     */
    protected function setFileAssociations(array $fields)
    {
        foreach ($fields as $field) {
            // skip non file or image types
            if (!in_array($field->getType(), ['files', 'images'])) {
                continue;
            }

            $name = CsvMigrationsUtils::createAssociationName('Burzum/FileStorage.FileStorage', $field->getName());
            $this->hasMany($name, [
                'className' => 'Burzum/FileStorage.FileStorage',
                'foreignKey' => 'foreign_key',
                'conditions' => [
                    'model' => $this->table(),
                    'model_field' => $field->getName(),
                ]
            ]);
        }
    }
    /**
     * Set associations based on migration.csv related type fields.
     *
     * @param array $config The configuration for the Table.
     * @param array $data All modules csv fields.
     * @return void
     */
    protected function setFieldAssociations(array $config, array $data)
    {
        foreach ($data as $module => $fields) {
            foreach ($fields as $field) {
                // skip non related type
                if (!in_array($field->getType(), ['related'])) {
                    continue;
                }

                // belongs-to association of the current module.
                if ($module === $config['table']) {
                    $name = CsvMigrationsUtils::createAssociationName($field->getAssocCsvModule(), $field->getName());
                    $this->belongsTo($name, [
                        'className' => $field->getAssocCsvModule(),
                        'foreignKey' => $field->getName()
                    ]);
                }

                // foreign key found in a related module.
                if ($field->getAssocCsvModule() === $config['table']) {
                    $name = CsvMigrationsUtils::createAssociationName($module, $field->getName());
                    $this->hasMany($name, [
                        'className' => $module,
                        'foreignKey' => $field->getName()
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
     * Convert field details into CSV object
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
     * Get a list of all modules
     *
     * @param string $path Path to look for modules at
     * @return array
     */
    protected function _getAllModules($path = null)
    {
        if (empty($path)) {
            $path = Configure::readOrFail('CsvMigrations.modules.path');
        }
        $result = Utility::findDirs($path);

        return $result;
    }

    /**
     * Get all modules data.
     *
     * @return array Modules, fields and fields types.
     */
    protected function _csvData()
    {
        $result = [];
        $path = Configure::readOrFail('CsvMigrations.modules.path');
        $modules = $this->_getAllModules($path);
        $csvFiles = [];
        if (empty($modules)) {
            return $result;
        }
        foreach ($modules as $module) {
            $mc = new ModuleConfig(ConfigType::MIGRATION(), $module);
            $config = (array)json_decode(json_encode($mc->parse()), true);

            if (empty($config)) {
                continue;
            }
            if (!isset($csvFiles[$module])) {
                $csvFiles[$module] = [];
            }
            $csvFiles[$module] = $config;
        }

        // Covers case where CsvMigration configuration files reside in a plugin
        $plugin = $this->_getPluginNameFromPath($path);

        if (is_null($plugin)) {
            // covers case where CsvMigration model and controller reside in
            // a plugin (even if configuration files reside in the APP level).
            $plugin = $this->_getPluginNameFromRegistryAlias();
        }

        foreach ($csvFiles as $csvModule => $config) {
            if (!is_null($plugin)) {
                $csvModule = $plugin . '.' . $csvModule;
            }
            $result[$csvModule] = $config;
        }

        return $result;
    }

    /**
     * Get the name of the plugin from its path
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
     * Get the name of the plugin from current Table's registry alias value
     *
     * @return string Name of plugin.
     */
    protected function _getPluginNameFromRegistryAlias()
    {
        list($plugin) = pluginSplit($this->registryAlias());

        return $plugin;
    }
}

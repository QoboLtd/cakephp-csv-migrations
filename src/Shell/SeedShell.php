<?php

namespace CsvMigrations\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use CsvMigrations\MigrationTrait;
use Faker\Factory;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class SeedShell extends Shell
{
    use MigrationTrait;

    protected $numberOfRecords = 1;
    protected $modules = [];
    protected $modulesPolpulatedWithData = [];
    protected $skipModules = [];

    /**
     * Configure option parser
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description('CSV Migration Seeder');

        return $parser;
    }

    /**
     * Main shell method
     *
     * @return void
     */
    public function main()
    {
        $path = Configure::readOrFail('CsvMigrations.modules.path');
        $this->modules = $this->_getAllModules($path);
        $csvFiles = $this->getModuleCsvData($this->modules);

        //check if module has relations
        $this->modules = $this->checkModuleRelations($csvFiles);

        //create index based on relations.
        $relationsIndex = $this->createRelationIndex($this->modules);

        //populate data to modules with relations
        $this->hierarchicalPopulateDataIntoModules($relationsIndex);

        $this->out("Done!");
    }

    /**
     * Get all the csv module's properties
     *
     * @param string $moduleName module name.
     * @return mixed|null
     */
    protected function getCSVModuleAttr($moduleName)
    {
        if (empty($this->modules[$moduleName])) {
            return null;
        }

        return $this->modules[$moduleName];
    }

    /**
     * Get field value based on type.
     *
     * @param string $type type.
     * @param string $moduleName module name.
     * @return null|string
     */
    protected function getFieldValueBasedOnType($type = '', $moduleName = '')
    {
        $faker = Factory::create();

        $value = null;

        switch ($type) {
            case 'uuid':
                $value = $faker->unique()->uuid;
                break;
            case 'url':
                $value = $faker->url;
                break;
            case 'time':
                $value = $faker->unique()->time('HH:mm');
                break;
            case 'string':
                $value = $faker->unique()->text(20);
                break;
            case 'text':
                $value = $faker->unique()->paragraph();
                break;
            case 'phone':
                $value = $faker->unique()->phoneNumber;
                break;
            case 'decimal':
            case 'money':
                $value = $faker->unique()->randomFloat();
                break;
            case 'integer':
                $value = $faker->unique()->numberBetween();
                break;
            case 'image':
                $value = $faker->unique()->imageUrl();
                break;
            case 'email':
                $value = $faker->unique()->email;
                break;
            case 'datetime':
                $value = $faker->unique()->dateTime;
                break;
            case 'date':
                $value = $faker->unique()->date('yyyy-MM-dd');
                break;
            case 'boolean':
                $value = $faker->unique()->boolean();
                break;
            default:
                if (strpos($type, 'list') !== false) {
                    //get list values
                    $listName = $this->getStringEnclosedInParenthesis($type);
                    $list = $this->getListData($moduleName, $listName);
                    if (empty($list) || count($list) == 0) {
                        $value = null;
                        break;
                    }
                    $value = $faker->randomElement($list);
                }
                if (strpos($type, 'related') !== false) {
                    //get list values
                    $moduleName = $this->getStringEnclosedInParenthesis($type);
                    $list = $this->getModuleIds($moduleName);
                    if (empty($list) || count($list) == 0) {
                        $value = null;
                        break;
                    }
                    $value = $faker->randomElement($list);
                }
        }

        return $value;
    }

    /**
     * Get Module ids in an array.
     *
     * @param string $moduleName module name
     * @return array
     */
    protected function getModuleIds($moduleName)
    {
        $table = TableRegistry::get($moduleName);
        $query = $table->find()->limit(100)->select($table->getPrimaryKey())->toArray();

        $keysArray = [];
        foreach ($query as $data) {
            $keysArray[] = $data->id;
        }

        return $keysArray;
    }

    /**
     * Get List (csv list) data.
     * @param string $module module name.
     * @param string $listName list name.
     * @return array
     */
    protected function getListData($module, $listName)
    {
        $listData = [];
        try {
            $mc = new ModuleConfig(ModuleConfig::CONFIG_TYPE_LIST, $module, $listName);
            $listData = $mc->parse()->items;
        } catch (\Exception $e) {
        }
        if (count($listData) == 0) {
            return $listData;
        }

        $keysArray = [];
        foreach ($listData as $data) {
            $keysArray[] = $data->value;
        }

        return $keysArray;
    }

    /**
     * Check module relations.
     *
     * @param array $modules modules
     * @return array
     */
    protected function checkModuleRelations(array $modules = [])
    {
        $modulesWithRelations = [];

        foreach ($modules as $name => $module) {
            $module['relations'] = [];
            foreach ($module as $field) {
                if (empty($field['type'])) {
                    continue;
                }
                if (strpos($field['type'], 'related') !== false) {
                    //get related module
                    $type = $this->getStringEnclosedInParenthesis($field['type']);
                    $module['relations'][] = $type;
                }
            }
            $modulesWithRelations[$name] = $module;
        }

        return $modulesWithRelations;
    }

    /**
     * Get string enclosed in parenthesis.
     * @param string $str string word.
     * @return mixed
     */
    protected function getStringEnclosedInParenthesis($str = '')
    {
        preg_match_all('/\((.+?)\)/', $str, $match);

        return $match[1][0];
    }

    /**
     * Get module csv data.
     * @param array $modules modules
     * @return array
     */
    protected function getModuleCsvData(array $modules = [])
    {
        $csvFiles = [];

        foreach ($modules as $module) {
            $mc = new ModuleConfig(ModuleConfig::CONFIG_TYPE_MIGRATION, $module);
            $config = (array)json_decode(json_encode($mc->parse()), true);

            if (empty($config)) {
                continue;
            }
            if (!isset($csvFiles[$module])) {
                $csvFiles[$module] = [];
            }
            $csvFiles[$module] = $config;
        }

        return $csvFiles;
    }

    /**
     * Insert data into module.
     *
     * @param string $moduleName module name.
     */
    protected function populateDataInModule($moduleName)
    {
        if (empty($moduleName)) {
            return;
        }

        if (in_array($moduleName, $this->skipModules)) {
            return;
        }

        $module = $this->getCSVModuleAttr($moduleName);

        if (empty($module)) {
            return;
        }

        $table = TableRegistry::get($moduleName);

        for ($count = 0; $count < $this->numberOfRecords; $count++) {
            $entity = $table->newEntity();

            foreach ($module as $fieldName => $fieldData) {
                if (empty($fieldData['type'])) {
                    continue;
                }

                $fieldValue = $this->getFieldValueBasedOnType($fieldData['type']);
                if (empty($fieldValue)) {
                    continue;
                }
                $entity->$fieldName = $fieldValue;
            }

            if ($table->save($entity)) {
                $id = $entity->id;
            }
        }
        $this->modulesPolpulatedWithData[] = $moduleName;
        $this->out($moduleName);
    }

    /**
     * Create relation index between modules.
     *
     * @param array $modules modules.
     * @return array
     */
    protected function createRelationIndex(array $modules = [])
    {
        $index = [];

        foreach ($modules as $moduleName => $module) {
            if (empty($module['relations']) || ! is_array($module['relations'])) {
                $index[$moduleName] = [];
                continue;
            }
            foreach ($module['relations'] as $relatedModule) {
                if (!empty($index[$relatedModule][$moduleName])) {
                    continue;
                }
                $index[$relatedModule][] = $moduleName;
            }
        }

        return $index;
    }

    /**
     * Hierarchical insert data into modules (based on index hierarchy).
     *
     * @param array $index index.
     */
    protected function hierarchicalPopulateDataIntoModules(array $index = [])
    {
        foreach ($index as $moduleName => $relationModule) {
            $this->checkHierarchyForModule($moduleName, $index);
        }
    }

    /**
     * Check the hierarchy for each module recursively and populate data.
     *
     * @param string $moduleName module name.
     * @param array $index index.
     */
    protected function checkHierarchyForModule($moduleName, array $index = [])
    {
        if (empty($moduleName)) {
            return;
        }

        if (in_array($moduleName, $this->modulesPolpulatedWithData)) {
            return;
        }

        if (!empty($index[$moduleName]) && is_array($index[$moduleName])) {
            foreach ($index[$moduleName] as $relatedModule) {
                $this->checkHierarchyForModule($relatedModule, $index);
            }
        }
        $this->populateDataInModule($moduleName);
    }
}

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
namespace CsvMigrations\Shell\Task;

use Bake\Shell\Task\BakeTask;
use Cake\Core\Configure;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;

/**
 * This class is responsible for baking relation's bootstrap configuration files.
 *
 * @property \Bake\Shell\Task\BakeTemplateTask $BakeTemplate
 */
class CsvRelationTask extends BakeTask
{
    /**
     * Path fragment for generated code.
     *
     * @var string
     */
    public $pathFragment = '';

    /**
     * Tasks to be loaded by this Task
     *
     * @var array
     */
    public $tasks = [
        'Bake.BakeTemplate'
    ];

    /**
     * Configure option parser
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->setDescription('Bakes Relation bootstrap configuration files');

        return $parser;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $name Module name
     */
    public function main(string $name = '')
    {
        parent::main();

        $path = Configure::readOrFail('CsvMigrations.modules.path');
        Utility::validatePath($path);

        $selection = $this->selection($path);
        $selection = $this->normalize($selection);
        $this->validate(implode('', $selection), $path);

        $this->bakeModuleConfig($selection, $path);
        $this->bakeDatabaseConfig($selection, $path);
    }

    /**
     * Interactive shell for modules selection.
     *
     * @param string $path Modules root path
     * @return string[]
     */
    private function selection(string $path) : array
    {
        $modules = $this->getModules($path);
        if (empty($modules)) {
            $this->abort('Aborting, system modules not found.');
        }

        $result[] = $this->in('Please select first related module:', $modules);
        $result[] = $this->in('Please select second related module:', $modules);
        while ('y' === $this->in('Would you like to select more modules?', ['y', 'n'])) {
            $result[] = $this->in('Please select another related module:', $modules);
        }

        return $result;
    }

    /**
     * System modules getter.
     *
     * @param string $path Modules root path
     * @return string[]
     */
    private function getModules(string $path) : array
    {
        $result = [];
        foreach (Utility::findDirs($path) as $module) {
            if (! $this->isModule($module)) {
                continue;
            }

            $result[] = $module;
        }

        return $result;
    }

    /**
     * Checks module validity.
     *
     * @param string $module Module name
     * @return bool
     */
    private function isModule(string $module) : bool
    {
        $config = (new ModuleConfig(ConfigType::MIGRATION(), $module, null, ['cacheSkip' => true]))->parse();
        $config = json_encode($config);
        if (false === $config) {
            return false;
        }
        $config = json_decode($config, true);
        if (empty($config)) {
            return false;
        }

        $config = (new ModuleConfig(ConfigType::MODULE(), $module, null, ['cacheSkip' => true]))->parse();
        if (! property_exists($config, 'table')) {
            return false;
        }
        if (! property_exists($config->table, 'type')) {
            return false;
        }

        if ('module' !== $config->table->type) {
            return false;
        }

        return true;
    }

    /**
     * Interactive input normalization.
     *
     * @param string[] $selection User input
     * @return string[]
     */
    private function normalize(array $selection) : array
    {
        $result = [];
        foreach ($selection as $module) {
            $result[] = $this->_camelize(strtolower(trim($module)));
        }

        $result = array_unique($result);
        asort($result);

        return $result;
    }

    /**
     * Validates relation name parameter.
     *
     * @param string $name Module name
     * @param string $path Modules root path
     * @return void
     */
    private function validate(string $name, string $path) : void
    {
        if (! ctype_alpha($name)) {
            $this->abort(sprintf('Invalid Relation name provided: %s', $name));
        }

        if (in_array($name, Utility::findDirs($path))) {
            $this->abort(sprintf('Relation %s already exists', $name));
        }
    }

    /**
     * Bake Relation configuration files.
     *
     * @param string[] $selection Modules selection
     * @param string $path Modules root path
     * @return bool
     */
    private function bakeModuleConfig(array $selection, string $path) : bool
    {
        reset($selection);
        $this->BakeTemplate->set(['display_field' => $this->_modelKey(current($selection))]);

        return $this->createFile(
            $path . implode('', $selection) . DS . 'config' . DS . 'config.dist.json',
            $this->BakeTemplate->generate('CsvMigrations.Relation/config/config')
        );
    }

    /**
     * Bake Database configuration files.
     *
     * @param string[] $selection Modules selection
     * @param string $path Modules root path
     * @return bool
     */
    private function bakeDatabaseConfig(array $selection, string $path) : bool
    {
        $fields = [];
        foreach ($selection as $module) {
            $fields[$this->_modelKey($module)] = [
                'name' => $this->_modelKey($module),
                'type' => sprintf('related(%s)', $module),
                'required' => true,
                'non-searchable' => false,
                'unique' => false
            ];
        }

        $this->BakeTemplate->set(['fields' => $fields]);

        return $this->createFile(
            $path . implode('', $selection) . DS . 'db' . DS . 'migration.dist.json',
            $this->BakeTemplate->generate('CsvMigrations.Relation/db/migration')
        );
    }
}

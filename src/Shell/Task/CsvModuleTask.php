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
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;
use Qobo\Utils\Utility;

/**
 * This class is responsible for baking module's bootstrap configuration files and MVC classes.
 */
class CsvModuleTask extends BakeTask
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
        $parser->description(
            'Bakes Module bootstrap configuration files and MVC classes'
        );
        $parser->addArgument('name', [
            'help' => 'The Module name to bake',
            'required' => true
        ]);

        return $parser;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $name Module name
     */
    public function main($name = '')
    {
        parent::main();

        $name = $this->_camelize($name);

        $this->validate($name, Configure::read('CsvMigrations.modules.path'));

        $this->bakeModuleConfig($name);
        $this->bakeDatabaseConfig($name);
        $this->bakeViewsConfig($name);
        $this->bakeController($name);
        $this->bakeApiController($name);
        $this->bakeTable($name);
        $this->bakeEntity($name);
        $this->bakeFeature($name);
    }

    /**
     * Validates required parameters such as module name and Modules configuration path.
     *
     * @param string $name Module name
     * @param string $path Modules configuration path
     * @return void
     */
    private function validate($name, $path)
    {
        Utility::validatePath($path);

        if (! ctype_alpha($name)) {
            $this->abort(sprintf('Invalid Module name provided: %s', $name));
        }

        if (in_array($name, Utility::findDirs($path))) {
            $this->abort(sprintf('Module %s already exists', $name));
        }
    }

    /**
     * Wrapper method responsible for baking the actual files.
     *
     * @param string $name Filename
     * @param string $template Template name
     * @param array $data Template data
     * @param string $suffix Filename suffix
     * @param array $options Extra options
     * @return bool
     */
    private function bake($name, $template, array $data = [], $suffix = '', array $options = [])
    {
        $this->BakeTemplate->set($data);
        $contents = $this->BakeTemplate->generate('CsvMigrations.' . $template);

        $path = empty($options['path']) ? $this->getPath() : $options['path'];
        $extension = empty($options['ext']) ? $options['ext'] = 'php' : $options['ext'];

        return $this->createFile($path . $name . $suffix . '.' . $extension, $contents);
    }

    /**
     * Bake Module configuration files.
     *
     * @param string $name Module name
     * @return void
     */
    private function bakeModuleConfig($name)
    {
        $options = [
            'path' => Configure::read('CsvMigrations.modules.path') . $name . DS . 'config' . DS,
            'ext' => 'json'
        ];

        $this->bake('config', 'Module/config/config', [], '', $options);
        $this->bake('fields', 'Module/config/fields', [], '', $options);
        $this->bake(
            'menus',
            'Module/config/menus',
            ['label' => Inflector::humanize($name), 'url' => DS . Inflector::dasherize($name) . DS],
            '',
            $options
        );
    }

    /**
     * Bake Database configuration files.
     *
     * @param string $name Module name
     * @return void
     */
    private function bakeDatabaseConfig($name)
    {
        $options = [
            'path' => Configure::read('CsvMigrations.modules.path') . $name . DS . 'db' . DS,
            'ext' => 'json'
        ];

        $this->bake('migration', 'Module/db/migration', [], '', $options);
    }

    /**
     * Bake Views configuration files.
     *
     * @param string $name Module name
     * @return void
     */
    private function bakeViewsConfig($name)
    {
        $options = [
            'path' => Configure::read('CsvMigrations.modules.path') . $name . DS . 'views' . DS,
            'ext' => 'json'
        ];

        $this->bake('add', 'Module/views/add', [], '', $options);
        $this->bake('edit', 'Module/views/edit', [], '', $options);
        $this->bake('index', 'Module/views/index', [], '', $options);
        $this->bake('view', 'Module/views/view', [], '', $options);
    }

    /**
     * Bake Controller class.
     *
     * @param string $name Module name
     * @return void
     */
    private function bakeController($name)
    {
        $this->pathFragment = 'Controller/';

        $this->bake($name, 'Controller/controller', ['name' => $name], 'Controller');
    }

    /**
     * Bake API Controller class.
     *
     * @param string $name Module name
     * @return void
     */
    private function bakeApiController($name)
    {
        $apiPaths = $this->getTargetApiPath();
        $this->pathFragment = $apiPaths['fragment'];

        $this->bake($name, 'Controller/Api/controller', array_merge(['name' => $name], $apiPaths), 'Controller');
    }

    /**
     * Bake Model/Table class.
     *
     * @param string $name Module name
     * @return void
     */
    private function bakeTable($name)
    {
        $this->pathFragment = 'Model/Table/';

        $this->bake($name, 'Model/table', ['name' => $name, 'table' => Inflector::underscore($name)], 'Table');
    }

    /**
     * Bake Model/Entity class.
     *
     * @param string $name Module name
     * @return void
     */
    private function bakeEntity($name)
    {
        $this->pathFragment = 'Model/Entity/';

        $this->bake($this->_entityName($name), 'Model/entity', ['name' => $this->_entityName($name)]);
    }

    /**
     * Bake Feature class.
     *
     * @param string $name Module name
     * @return void
     */
    private function bakeFeature($name)
    {
        $this->pathFragment = 'Feature/Type/Module/';

        $this->bake($name, 'Feature/feature', ['name' => $name], 'Feature');
    }

    /**
     * Get Target API Path for API Controllers
     *
     * We create API controllers for the most recent API version.
     *
     * @return array $result containing path Fragment for baking.
     */
    protected function getTargetApiPath()
    {
        $result = [
            'fragment' => 'Controller/Api',
            'namespace' => 'App\Controller\Api',
        ];

        $versions = Utility::getApiVersions();

        if (empty($versions)) {
            return $result;
        }

        $recent = end($versions);

        if (preg_match('/^api(.*)$/', $recent['prefix'], $matches)) {
            $postfix = strtoupper($matches[1]);
            $result['fragment'] .= $postfix . '/';
            $result['namespace'] .= str_replace('/', '\\', $postfix);
        }

        return $result;
    }
}

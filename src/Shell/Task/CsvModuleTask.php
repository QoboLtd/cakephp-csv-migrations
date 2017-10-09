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
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;

/**
 * CSV Module baking migration task, used to extend CakePHP's bake functionality.
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
     * {@inheritDoc}
     */
    public function main($name = null)
    {
        parent::main();

        $name = $this->_getName($name);

        if (empty($name)) {
            $this->abort('Please provide the module name.');
        }

        if (empty(Configure::read('CsvMigrations.modules.path'))) {
            $this->abort('CSV modules path is not defined.');
        }

        $this->bake($name);
    }

    /**
     * Generate code for the given module name.
     *
     * @param string $name The module name to generate.
     * @return void
     */
    public function bake($name)
    {
        $this->_generateConfigFiles($name);

        // bake controller
        $this->pathFragment = 'Controller/';
        $controllerName = $this->_camelize($name);
        $data = [
            'name' => $controllerName
        ];
        $this->_bakeTemplate($controllerName, 'Controller/controller', $data, 'Controller');

        // bake api controller
        $this->pathFragment = 'Controller/Api/';
        $this->_bakeTemplate($controllerName, 'Controller/Api/controller', $data, 'Controller');

        // bake model table
        $this->pathFragment = 'Model/Table/';
        $tableName = $this->_modelNameFromKey($name);
        $data = [
            'name' => $tableName,
            'table' => Inflector::underscore($name)
        ];
        $this->_bakeTemplate($tableName, 'Model/table', $data, 'Table');

        // bake model entity
        $this->pathFragment = 'Model/Entity/';
        $entityName = $this->_entityName($name);
        $data = [
            'name' => $entityName
        ];
        $this->_bakeTemplate($entityName, 'Model/entity', $data);
    }

    /**
     * Generates csv module configuration files.
     *
     * @param string $name Module name
     * @return void
     */
    protected function _generateConfigFiles($name)
    {
        $templatePath = current(App::path('Template', 'CsvMigrations')) . 'Bake/config/CsvModule';
        $templatePath = str_replace('/', DS, $templatePath);

        if (!file_exists($templatePath)) {
            $this->abort('CsvModule Bake template does not exist.');
        }

        $path = Configure::read('CsvMigrations.modules.path') . $this->_camelize($name) . DS;

        if (file_exists($path)) {
            $this->abort(Inflector::humanize(Inflector::underscore($name)) . ' module already has configuration files.');
        }

        $folder = new Folder($templatePath);

        $this->out('Generating config files in ' . $path);
        if (!$folder->copy($path)) {
            $this->abort('Failure');
        }

        $this->success('Success');
    }

    /**
     * Generate the controller code
     *
     * @param string $name The name of the controller.
     * @param string $templateName Template name.
     * @param array $data The data to turn into code.
     * @param string $fileSuffix File suffix.
     * @return string The generated controller file.
     */
    protected function _bakeTemplate($name, $templateName, array $data, $fileSuffix = '')
    {
        $this->BakeTemplate->set($data);

        $contents = $this->BakeTemplate->generate('CsvMigrations.' . $templateName);

        $path = $this->getPath();

        $filename = $path . $name . $fileSuffix . '.php';

        $this->createFile($filename, $contents);

        return $contents;
    }
}

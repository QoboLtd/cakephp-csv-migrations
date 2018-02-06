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
use Cake\Filesystem\File;
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

        if (empty(Configure::read('CsvMigrations.features.module.path'))) {
            $this->abort('Features path is not defined');
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

        $this->pathFragment = Configure::read('CsvMigrations.features.module.path_fragment');
        $featureName = $this->_modelNameFromKey($name);
        $data = [
            'name' => $featureName,
        ];
        $this->_bakeTemplate($featureName, Configure::read('CsvMigrations.features.module.template'), $data, 'Feature');
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

        // Once the files are copied. Go through them and generate twig ones.
        if (!$this->_processConfigFiles($name, $path)) {
            $this->abort('Couldn\'t process twig files within module config dir');
        }

        $this->success('Success');
    }

    /**
     * Process Config directory for the module
     *
     * @param string $name of the module
     * @param string $path of the module location
     *
     * @return bool $result on whether the conf files were processed.
     */
    protected function _processConfigFiles($name, $path)
    {
        $result = false;
        $path .= 'config' . DS;

        $dir = new Folder($path);

        $contents = $dir->read(true, true);

        if (empty($contents[1])) {
            return $result;
        }

        foreach ($contents[1] as $file) {
            if (substr($file, -5) !== '.twig') {
                continue;
            }

            $filename = substr($file, 0, -5);
            $methodName = 'set' . Inflector::camelize($filename) . 'Template';

            if (!method_exists($this, $methodName)) {
                throw new \RuntimeException("No method for setting data for $file exists");
            }

            $data = $this->$methodName(['name' => $name]);
            $templateName = 'config' . DS . 'CsvModule' . DS . 'config' . DS . $filename;

            $contents = $this->_bakeTemplate($filename, $templateName, $data, '', [
                'ext' => $data['ext'],
                'path' => $data['path'],
            ]);

            if (!empty($contents)) {
                $result = $this->_deleteFile($file, $data['path']);
            }
        }

        return $result;
    }

    /**
     * Remove template file from generated module
     *
     * @param string $file name of twig template
     * @param string $path of the destination directory
     *
     * @return bool $result on file deletion.
     */
    protected function _deleteFile($file, $path)
    {
        $result = false;

        $file = new File($path . $file);
        $result = $file->delete();
        $file->close();

        return $result;
    }

    /**
     * Template Data setter
     *
     * @param array $options passing module's name
     *
     * @return array $data with required paths and vars
     */
    protected function setMenusTemplate(array $options = [])
    {
        $data = [
            'label' => Inflector::humanize($options['name']),
            'url' => DS . Inflector::dasherize($options['name']) . DS,
            'path' => CONFIG . 'Modules' . DS . $options['name'] . DS . 'config' . DS,
            'ext' => '.json',
        ];

        return $data;
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
    protected function _bakeTemplate($name, $templateName, array $data, $fileSuffix = '', array $options = [])
    {
        $this->BakeTemplate->set($data);
        $contents = $this->BakeTemplate->generate('CsvMigrations.' . $templateName);

        $path = empty($options['path']) ? $this->getPath() : $options['path'];

        if (empty($options['ext'])) {
            $options['ext'] = '.php';
        }

        $filename = $path . $name . $fileSuffix . $options['ext'];

        $this->createFile($filename, $contents);

        return $contents;
    }
}

<?php
namespace CsvMigrations\Shell\Task;

use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;
use CsvMigrations\PathFinder\MigrationPathFinder;
use Migrations\Shell\Task\MigrationTask;
use Phinx\Util\Util;

/**
 * CsvMigrations baking migration task, used to extend CakePHP's bake functionality.
 */
class CsvMigrationTask extends MigrationTask
{
    /**
     * Timestamp
     * @var string
     */
    private $__timestamp;

    /**
     * {@inheritDoc}
     */
    public function main($name = null)
    {
        if (empty(Configure::read('CsvMigrations.modules.path'))) {
            $this->abort('CSV modules path is not defined.');
        }

        $this->__timestamp = Util::getCurrentTimestamp();

        $modules = $this->_getCsvModules();
        if (empty($modules)) {
            $this->abort('There are no CSV modules in this system');
        }

        // output system's available csv modules
        if (!in_array($this->_camelize($name), $modules)) {
            $this->out('Possible modules based on your current csv configuration:');
            foreach ($modules as $module) {
                $this->out('- ' . $module);
            }

            return true;
        }

        parent::main($name);
    }

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return 'CSV Migration';
    }

    /**
     * {@inheritDoc}
     */
    public function fileName($name)
    {
        $name = $this->getMigrationName($name);
        list($table) = $this->_getVars($this->args[0]);

        return $this->__timestamp . '_' . Inflector::camelize($name) . $this->_getLastModifiedTime($table) . '.php';
    }

    /**
     * {@inheritDoc}
     */
    public function template()
    {
        return 'CsvMigrations.csv_migration';
    }

    /**
     * {@inheritDoc}
     */
    public function templateData()
    {
        list($table, $name) = $this->_getVars($this->BakeTemplate->viewVars['name']);

        return [
            'table' => $table,
            'name' => $name
        ];
    }

    /**
     * Get CSV module names from defined modules directory.
     *
     * @return array
     */
    protected function _getCsvModules()
    {
        $dir = new Folder(Configure::read('CsvMigrations.modules.path'));
        $folders = $dir->read(true)[0];

        return (array)$folders;
    }

    /**
     * Returns variables for bake template.
     *
     * @param  string $tableName Table name
     * @return array
     */
    protected function _getVars($tableName)
    {
        $table = Inflector::tableize($tableName);

        $name = Inflector::camelize($tableName) . $this->_getLastModifiedTime($table);

        return [$table, $name];
    }

    /**
     * Get csv file's last modified time.
     *
     * @param  string $tableName target table name
     * @return string
     */
    protected function _getLastModifiedTime($tableName)
    {
        $tableName = Inflector::camelize($tableName);

        $pathFinder = new MigrationPathFinder;
        $path = $pathFinder->find($tableName);

        // Unit time stamp to YYYYMMDDhhmmss
        $result = date('YmdHis', filemtime($path));

        return $result;
    }
}

<?php
namespace CsvMigrations\Shell\Task;

use Cake\Core\Configure;
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
     * Tasks to be loaded by this Task
     *
     * @var array
     */
    public $tasks = [
        'Bake.Model'
    ];

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
        $this->__timestamp = Util::getCurrentTimestamp();

        $this->Model->connection = $this->connection;
        $allTables = $this->Model->listUnskipped();
        if (!in_array(Inflector::tableize($name), $allTables)) {
            $this->out('Possible tables based on your current database:');
            foreach ($allTables as $table) {
                $this->out('- ' . $this->_camelize($table));
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

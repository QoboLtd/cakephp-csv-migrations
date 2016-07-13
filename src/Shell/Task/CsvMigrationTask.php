<?php
namespace CsvMigrations\Shell\Task;

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use CsvMigrations\View\Exception\MissingCsvException;
use Migrations\Shell\Task\MigrationTask;
use Phinx\Util\Util;

/**
 * CsvMigrations baking migration task, used to extend CakePHP's bake functionality.
 */
class CsvMigrationTask extends MigrationTask
{
    /**
     * File extension
     */
    const FILE_EXTENSION = 'csv';

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
        list(, $table) = $this->_getVars($this->args[0]);

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
        list($action, $table, $name) = $this->_getVars($this->BakeTemplate->viewVars['name']);

        return [
            'action' => $action,
            'table' => $table,
            'name' => $name
        ];
    }

    /**
     * Returns variables for bake template.
     *
     * @param  string $actionName action name
     * @return array
     */
    protected function _getVars($actionName)
    {
        $action = $this->detectAction($actionName);

        if (empty($action)) {
            $table = $actionName;
            $action = 'create_table';
        } else {
            list($action, $table) = $action;
        }
        $table = Inflector::tableize($table);

        $name = Inflector::camelize($actionName) . $this->_getLastModifiedTime($table);

        return [$action, $table, $name];
    }

    /**
     * Get csv file's last modified time.
     *
     * @param  string $tableName target table name
     * @return string
     * @throws \CsvMigrations\View\Exception\MissingCsvException
     */
    protected function _getLastModifiedTime($tableName)
    {
        $tableName = Inflector::humanize($tableName);

        $path = Configure::readOrFail('CsvMigrations.migrations.path') . $tableName;
        $path .= DS . Configure::readOrFail('CsvMigrations.migrations.filename') . '.' . static::FILE_EXTENSION;

        if (!file_exists($path)) {
            throw new MissingCsvException($tableName);
        }

        return filemtime($path);
    }
}

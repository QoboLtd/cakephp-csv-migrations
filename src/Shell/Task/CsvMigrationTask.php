<?php
namespace CsvMigrations\Shell\Task;

use Cake\Utility\Inflector;
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

        return $this->__timestamp . '_' . Inflector::camelize($name) . $this->__timestamp . '.php';
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
        $className = $this->BakeTemplate->viewVars['name'];
        $action = $this->detectAction($className);

        if (empty($action)) {
            $table = $className;
            $action = 'create_table';
        } else {
            list($action, $table) = $action;
        }
        $table = Inflector::tableize($table);

        $name = Inflector::camelize($className) . $this->__timestamp;

        return [
            'action' => $action,
            'table' => $table,
            'name' => $name
        ];
    }
}

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

        return $this->__timestamp . '_' . Inflector::camelize($name) . $this->_getUniqueName() . '.php';
    }

    /**
     * {@inheritDoc}
     */
    public function template()
    {
        return 'CsvMigrations.csv_migration';
    }

    /**
     * {@inheritdoc}
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

        $name = Inflector::camelize($className) . $this->_getUniqueName();

        return [
            'action' => $action,
            'table' => $table,
            'name' => $name
        ];
    }

    /**
     * Generate and return unique alphabetic string.
     *
     * @return string
     */
    protected function _getUniqueName()
    {
        $alphas = range('A', 'Z');
        $numbers = str_split($this->__timestamp);

        array_walk($numbers, function (&$number, $k, $alphas) {
            if (array_key_exists($number, $alphas)) {
                $number = $alphas[$number];
            }
        }, $alphas);

        return implode('', $numbers);
    }
}

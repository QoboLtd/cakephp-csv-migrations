<?php
namespace CsvMigrations;

use Cake\ORM\Table as BaseTable;

/**
 * Accounts Model
 *
 */
class Table extends BaseTable
{
    use CsvMigrationsTableTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->_setAssociationsFromCsv($config);
    }
}

<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use CsvMigrations\Table;

class PanelTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('panel');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

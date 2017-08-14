<?php
namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class LeadsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('leads');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

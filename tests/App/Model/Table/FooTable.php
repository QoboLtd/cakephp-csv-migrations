<?php
namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class FooTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('foo');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

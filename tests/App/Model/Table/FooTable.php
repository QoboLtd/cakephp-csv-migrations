<?php
namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class FooTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('foo');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

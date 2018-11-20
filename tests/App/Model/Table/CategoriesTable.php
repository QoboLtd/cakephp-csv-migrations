<?php
namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class CategoriesTable extends Table
{
    public function initialize(array $config) : void
    {
        parent::initialize($config);

        $this->setTable('categories');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

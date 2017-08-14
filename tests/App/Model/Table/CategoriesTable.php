<?php
namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class CategoriesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('categories');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

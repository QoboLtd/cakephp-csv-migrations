<?php
namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class AuthorsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('authors');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

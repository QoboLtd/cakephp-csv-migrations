<?php

namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class AuthorsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('authors');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

<?php

namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class TagsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('tags');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

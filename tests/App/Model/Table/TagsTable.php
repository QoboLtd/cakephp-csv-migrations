<?php
namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class TagsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('tags');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

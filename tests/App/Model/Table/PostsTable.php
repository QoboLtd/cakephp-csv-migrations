<?php
namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class PostsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('posts');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

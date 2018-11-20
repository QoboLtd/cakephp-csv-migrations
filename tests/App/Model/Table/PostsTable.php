<?php
namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class PostsTable extends Table
{
    public function initialize(array $config) : void
    {
        parent::initialize($config);

        $this->setTable('posts');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

<?php
namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class ArticlesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('articles');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

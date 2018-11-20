<?php
namespace CsvMigrations\Test\App\Model\Table;

use CsvMigrations\Table;

class ArticlesTable extends Table
{
    public function initialize(array $config) : void
    {
        parent::initialize($config);

        $this->setTable('articles');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

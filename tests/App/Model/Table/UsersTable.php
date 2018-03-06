<?php
namespace CsvMigrations\Test\App\Model\Table;

use Cake\ORM\Table;

/**
 * Users Model
 */
class UsersTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('users');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

<?php
namespace CsvMigrations\Test\App\Model\Table;

use Cake\ORM\Table;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\FieldTrait;

/**
 * Users Model
 */
class UsersTable extends Table
{
    use ConfigurationTrait;
    use FieldTrait;

    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('users');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        // set table/module configuration
        $this->setConfig($this->table());
    }
}

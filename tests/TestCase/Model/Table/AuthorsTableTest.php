<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\Table;

class AuthorsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('authors');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
        $config['table'] = 'Authors';
        $this->_setAssociations($config);
    }
}

class AuthorsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var CsvMigrations\Test\TestCase\Model\Table\FooTable
     */
    public $AuthorsTable;

    public $fixtures = [
        'plugin.csv_migrations.authors',
        'plugin.csv_migrations.posts',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $dir = dirname(__DIR__) . DS . '..' . DS . '..' . DS . 'data' . DS . 'Modules' . DS;
        Configure::write('CsvMigrations.modules.path', $dir);

        $config = TableRegistry::exists('Authors') ? [] : ['className' => 'CsvMigrations\Test\TestCase\Model\Table\AuthorsTable'];
        $this->AuthorsTable = TableRegistry::get('Authors', $config);
    }
}

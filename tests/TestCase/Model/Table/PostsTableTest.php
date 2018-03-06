<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\Table;

class PostsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('posts');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
        $config['table'] = 'Posts';
        $this->_setAssociations($config);
    }
}

class PostsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var CsvMigrations\Test\TestCase\Model\Table\FooTable
     */
    public $PostsTable;

    public $fixtures = [
        'plugin.csv_migrations.posts',
        'plugin.csv_migrations.authors',
        'plugin.csv_migrations.tags',
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

        $config = TableRegistry::exists('Posts') ? [] : ['className' => 'CsvMigrations\Test\TestCase\Model\Table\PostsTable'];
        $this->PostsTable = TableRegistry::get('Posts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->table);

        parent::tearDown();
    }

    {
    }
}

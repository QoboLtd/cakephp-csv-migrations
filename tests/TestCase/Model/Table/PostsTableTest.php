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

    public function testGetAssociationFields()
    {
        foreach ($this->PostsTable->associations() as $association) {
            $result = $this->PostsTable->getAssociationFields($association);
            if ('OwnerAuthors' == $association->name()) {
                $this->assertNotEmpty($result);
                $expectedFields = array_values($result['fields']);
                $this->assertEquals($expectedFields, ['name', 'description', 'created', 'modified']);
            }

            if ('Tags' == $association->name()) {
                $this->assertEquals('tag_id', $result['foreign_key']);
            }
        }
    }

    public function testGetAssociationObject()
    {
        $result = $this->PostsTable->getAssociationObject('Posts', 'OwnerAuthors');
        $this->assertEquals('OwnerAuthors', $result->name());
    }
}

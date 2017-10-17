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
        $result = $this->PostsTable->getAssociationObject('OwnerAuthors');
        $this->assertEquals('OwnerAuthors', $result->name());
    }

    public function testGetRelatedEntitiesOrder()
    {
        $fields = [
            'name',
            'description',
            'created',
            'modified'
        ];

        $data = [
            'columns' => [
                'name' => [
                    'data' => 0,
                    'name' => '',
                    'searchable' => true,
                    'orderable' => true,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ]
                ],
                'description' => [
                    'data' => 1,
                    'name' => '',
                    'searchable' => true,
                    'orderable' => true,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ],
                ],
            ],
            'order' => [
                ['column' => 0, 'dir' => 'asc'],
            ]
        ];

        $result = $this->PostsTable->getRelatedEntitiesOrder($this->PostsTable, $fields, $data);
        $this->assertEquals($result, ['Posts.name' => 'asc']);
    }

    public function testGetOneToManyCount()
    {
        $query = $this->PostsTable->find();

        $result = $this->PostsTable->getOneToManyCount($query, [
            'conditions' => [
                'id' => '00000000-0000-0000-0000-000000000001',
            ],
        ]);

        $this->assertEquals($result, 1);
        $this->assertTrue(is_numeric($result));
    }
}

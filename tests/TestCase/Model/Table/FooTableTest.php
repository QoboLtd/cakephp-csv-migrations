<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Table;

/**
 * Foo Model
 *
 */
class FooTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('foo');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }
}

/**
 * CsvMigrations\Test\Data\Model\Table\FooTable Test Case
 */
class FooTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \CsvMigrations\Model\Table\FooTable
     */
    public $FooTable;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $dir = dirname(__DIR__) . DS . '..' . DS . '..' . DS . 'data' . DS . 'CsvMigrations' . DS;

        /*
        point to test data
         */
        Configure::write('CsvMigrations.migrations.path', $dir . 'migrations' . DS);
        Configure::write('CsvMigrations.lists.path', $dir . 'lists' . DS);
        Configure::write('CsvMigrations.migrations.filename', 'migration.dist');

        $config = TableRegistry::exists('Foo') ? [] : ['className' => 'CsvMigrations\Test\TestCase\Model\Table\FooTable'];
        $this->FooTable = TableRegistry::get('Foo', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->FooTable);

        parent::tearDown();
    }

    public function testGetConfig()
    {
        $this->assertSame(
            $this->FooTable->getConfig(),
            [
                'table' => [
                    'alias' => 'Foobar',
                    'searchable' => '1',
                    'display_field' => 'name'
                ]
            ]
        );
    }

    /**
     * @dataProvider moduleAliasProvider
     */
    public function testModuleAlias($alias, $expected)
    {
        $this->assertSame($this->FooTable->moduleAlias($alias), $expected);
    }

    /**
     * @dataProvider searchableFieldsProvider
     */
    public function testGetSearchableFields($expected)
    {
        $this->assertSame($this->FooTable->getSearchableFields(), $expected);
    }

    /**
     * @dataProvider searchableFieldPropertiesProvider
     */
    public function testGetSearchableFieldProperties($expected)
    {
        $fields = $this->FooTable->getSearchableFields();
        $this->assertSame($this->FooTable->getSearchableFieldProperties($fields), $expected);
    }

    public function testGetSearchableFieldPropertiesEmptyFields()
    {
        $this->assertSame($this->FooTable->getSearchableFieldProperties([]), []);
    }

    public function moduleAliasProvider()
    {
        return [
            [null, 'Foobar'],
            ['Foo', 'Foo'],
            [null, 'Foo']
        ];
    }

    public function searchableFieldsProvider()
    {
        return [
            [
                ['id', 'name', 'status', 'type', 'gender', 'city', 'country', 'cost', 'birthdate', 'created', 'modified']
            ]
        ];
    }

    public function searchableFieldPropertiesProvider()
    {
        return [
            [
                [
                    'id' => [
                        'type' => 'uuid'
                    ],
                    'name' => [
                        'type' => 'string'
                    ],
                    'status' => [
                        'type' => 'list',
                        'fieldOptions' => [
                            'active' => 'Active',
                            'inactive' => 'Inactive'
                        ]
                    ],
                    'type' => [
                        'type' => 'list',
                        'fieldOptions' => [
                            'bronze' => 'Bronze',
                            'bronze.new' => 'New',
                            'bronze.used' => 'Used',
                            'silver' => 'Silver',
                            'silver.new' => 'New',
                            'silver.used' => 'Used',
                            'gold' => 'Gold',
                            'gold.new' => 'New',
                            'gold.used' => 'Used'
                        ]
                    ],
                    'gender' => [
                        'type' => 'list',
                        'fieldOptions' => [
                            'm' => 'Male',
                            'f' => 'Female'
                        ]
                    ],
                    'city' => [
                        'type' => 'list',
                        'fieldOptions' => [
                            'limassol' => 'Limassol',
                            'new_york' => 'New York',
                            'london' => 'London'
                        ]
                    ],
                    'country' => [
                        'type' => 'list',
                        'fieldOptions' => [
                            'cy' => 'Cyprus',
                            'usa' => 'USA',
                            'uk' => 'United Kingdom'
                        ]
                    ],
                    'cost' => [
                        'type' => 'money'
                    ],
                    'birthdate' => [
                        'type' => 'date'
                    ],
                    'created' => [
                        'type' => 'datetime'
                    ],
                    'modified' => [
                        'type' => 'datetime'
                    ]
                ]
            ]
        ];
    }
}

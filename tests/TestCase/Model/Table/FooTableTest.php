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
 * CsvMigrations\Test\TestCase\Model\Table\FooTable Test Case
 */
class FooTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var CsvMigrations\Test\TestCase\Model\Table\FooTable
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

    /**
     * @dataProvider fieldsDefinitionsProvider
     */
    public function testGetFieldsDefinitions($name, $expected)
    {
        $this->assertEquals($expected, $this->FooTable->getFieldsDefinitions($name));
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
                [
                    'id',
                    'name',
                    'status',
                    'type',
                    'gender',
                    'city',
                    'country',
                    'cost',
                    'birthdate',
                    'created',
                    'modified',
                    'garden_area',
                    'is_primary',
                    'start_time'
                ]
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
                            'bronze.new' => ' - New',
                            'bronze.used' => ' - Used',
                            'silver' => 'Silver',
                            'silver.new' => ' - New',
                            'silver.used' => ' - Used',
                            'gold' => 'Gold',
                            'gold.new' => ' - New',
                            'gold.used' => ' - Used'
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
                    ],
                    'garden_area' => [
                        'type' => 'metric'
                    ],
                    'is_primary' => [
                        'type' => 'boolean'
                    ],
                    'start_time' => [
                        'type' => 'time'
                    ]
                ]
            ]
        ];
    }

    public function fieldsDefinitionsProvider()
    {
        return [
            [
                null,
                [
                    'id' => [
                        'name' => 'id',
                        'type' => 'uuid',
                        'required' => '',
                        'non-searchable' => '',
                        'unique' => false
                    ],
                    'description' => [
                        'name' => 'description',
                        'type' => 'text',
                        'required' => '',
                        'non-searchable' => true,
                        'unique' => false
                    ],
                    'name' => [
                        'name' => 'name',
                        'type' => 'string',
                        'required' => '1',
                        'non-searchable' => '',
                        'unique' => true
                    ],
                    'status' => [
                        'name' => 'status',
                        'type' => 'list(foo_statuses)',
                        'required' => '1',
                        'non-searchable' => '',
                        'unique' => false
                    ],
                    'type' => [
                        'name' => 'type',
                        'type' => 'list(foo_types)',
                        'required' => '1',
                        'non-searchable' => '',
                        'unique' => false
                    ],
                    'gender' => [
                        'name' => 'gender',
                        'type' => 'list(genders)',
                        'required' => '',
                        'non-searchable' => '',
                        'unique' => false
                    ],
                    'city' => [
                        'name' => 'city',
                        'type' => 'list(cities)',
                        'required' => '',
                        'non-searchable' => '',
                        'unique' => false
                    ],
                    'country' => [
                        'name' => 'country',
                        'type' => 'list(countries)',
                        'required' => '',
                        'non-searchable' => '',
                        'unique' => false
                    ],
                    'cost' => [
                        'name' => 'cost',
                        'type' => 'money(currencies)',
                        'required' => '',
                        'non-searchable' => '',
                        'unique' => false
                    ],
                    'birthdate' => [
                        'name' => 'birthdate',
                        'type' => 'date',
                        'required' => '',
                        'non-searchable' => '',
                        'unique' => false
                    ],
                    'created' => [
                        'name' => 'created',
                        'type' => 'datetime',
                        'required' => '',
                        'non-searchable' => '',
                        'unique' => false
                    ],
                    'modified' => [
                        'name' => 'modified',
                        'type' => 'datetime',
                        'required' => '',
                        'non-searchable' => '',
                        'unique' => false
                    ],
                    'garden_area' => [
                        'name' => 'garden_area',
                        'type' => 'metric(units_area)',
                        'required' => '',
                        'non-searchable' => '',
                        'unique' => false
                    ],
                    'is_primary' => [
                        'name' => 'is_primary',
                        'type' => 'boolean',
                        'required' => '',
                        'non-searchable' => '',
                        'unique' => false
                    ],
                    'start_time' => [
                        'name' => 'start_time',
                        'type' => 'time',
                        'required' => '',
                        'non-searchable' => '',
                        'unique' => false
                    ]
                ]
            ]
        ];
    }
}

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
        Configure::write('CsvMigrations.migrations.filename', 'migration');

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
                    'searchable' => true,
                    'display_field' => 'name',
                    'typeahead_fields' => 'name,foobar',
                    'lookup_fields' => 'foo,bar,baz',
                ],
                'virtualFields' => [
                    'name' => 'full_name',
                ],
                'parent' => [
                    'module' => 'TestModule',
                    'redirect' => 'self',

                ],
                'associations' => [
                    'hide_associations' => 'TestTable',
                ],

                'associationLabels' => [
                    'FieldIdTable' => 'Table',
                    'AnotherIdTableTwo' => 'Pretty Table'
                ]
            ]
        );
    }

    public function testModuleAliasGetter()
    {
        $this->assertSame('Foobar', $this->FooTable->moduleAlias());
    }

    /**
     * @dataProvider moduleAliasProvider
     */
    public function testModuleAliasSetter($alias, $expected)
    {
        $this->assertSame($expected, $this->FooTable->moduleAlias($alias));
    }

    public function testModuleAliasGetterDefault()
    {
        $this->FooTable->moduleAlias('Foo');
        $this->assertSame('Foo', $this->FooTable->moduleAlias(null));
    }

    public function testGetReminderFields()
    {
        $fields = $this->FooTable->getReminderFields();
        $this->assertTrue(is_array($fields), "reminderFields is not an array");
        $this->assertEquals('reminder_date', $fields[0]['name'], "Field reminder is incorrectly matched");
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
            ['Foo', 'Foo']
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
                    'reminder_date' => [
                        'name' => 'reminder_date',
                        'type' => 'reminder',
                        'required' => null,
                        'non-searchable' => null,
                        'unique' => null,
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
                    ],
                    'balance' => [
                        'name' => 'balance',
                        'type' => 'decimal(12.4)',
                        'required' => '',
                        'non-searchable' => '',
                        'unique' => false
                    ]
                ]
            ]
        ];
    }
}

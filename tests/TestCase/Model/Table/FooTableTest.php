<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
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

        $dir = dirname(__DIR__) . DS . '..' . DS . '..' . DS . 'data' . DS . 'Modules' . DS;
        Configure::write('CsvMigrations.modules.path', $dir);

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

    public function testSetCurrentUser()
    {
        $user = [
            'id' => 123,
            'username' => 'some_foo_user',
        ];
        $result = $this->FooTable->setCurrentUser($user);
        $this->assertEquals($user, $result, "setCurrentUser did not return the correct user");

        $result = $this->FooTable->getCurrentUser();
        $this->assertEquals($user, $result, "getCurrentUser did not return the correct user");
    }

    public function testGetParentRedirectUrl()
    {
        $result = $this->FooTable->getParentRedirectUrl($this->FooTable);
        $this->assertTrue(is_array($result));
    }

    public function testGetConfig()
    {
        $expected = [
                'table' => [
                    'alias' => 'Foobar',
                    'searchable' => true,
                    'display_field' => 'name',
                    'typeahead_fields' => [
                        'name',
                        'foobar',
                    ],
                    'lookup_fields' => [
                        'foo',
                        'bar',
                        'baz',
                    ],
                    'allow_reminders' => [
                        'Users',
                        'Contacts',
                    ],
                    'basic_search_fields' => [
                        'first_name',
                        'last_name',
                    ],
                    'icon' => 'cube',
                    'translatable' => false,
                ],
                'virtualFields' => [
                    'name' => [
                        'foo',
                        'bar',
                    ],
                ],
                'manyToMany' => [
                    'modules' => [
                        'Users',
                    ],
                ],
                'parent' => [
                    'module' => 'TestModule',
                    'redirect' => 'self',

                ],
                'associations' => [
                    'hide_associations' => [
                        'TestTable',
                        'AnotherTable',
                    ],
                ],
                'associationLabels' => [
                    'FieldIdTable' => 'Table',
                    'AnotherIdTableTwo' => 'Pretty Table'
                ],
                'notifications' => [
                    'enable' => true,
                    'ignored_fields' => [
                        'created',
                        'modified',
                    ],
                ],
            ];

        $actual = $this->FooTable->getConfig();
        foreach ($expected as $section => $options) {
            $this->assertTrue(isset($actual[$section]), "getConfig() did not return section [$section]");
            foreach ($options as $name => $value) {
                $this->assertTrue(isset($actual[$section][$name]), "getConfig() did not return option [$name] in section [$section]");
                $this->assertEquals($value, $actual[$section][$name], "getConfig() returned incorrect value for option [$name] in section [$section]");
            }
        }
    }

    public function testLookupFields()
    {
        $expected = ['foo', 'bar', 'baz'];
        $actual = $this->FooTable->getConfig(ConfigurationTrait::$CONFIG_OPTION_LOOKUP_FIELDS);
        $this->assertTrue(is_array($actual), "Non-array returned from lookupFields");
        $this->assertEquals($expected, $actual, "Incorrect value returned from lookupFields");
    }

    public function testTypeaheadFields()
    {
        $expected = ['name', 'foobar'];
        $actual = $this->FooTable->getConfig(ConfigurationTrait::$CONFIG_OPTION_TYPEAHEAD_FIELDS);
        $this->assertTrue(is_array($actual), "Non-array returned from typeaheadFields");
        $this->assertEquals($expected, $actual, "Incorrect value returned from typeaheadFields");
    }

    public function testGetVirtualFields()
    {
        $expected = ['name' => ['foo', 'bar']];
        $actual = $this->FooTable->getConfig(ConfigurationTrait::$CONFIG_OPTION_VIRTUAL_FIELDS);
        $this->assertTrue(is_array($actual), "Non-array returned from virtualFields");
        $this->assertEquals($expected, $actual, "Incorrect value returned from virtualFields");
    }

    public function testHiddenAssociations()
    {
        $expected = ['TestTable', 'AnotherTable'];
        $actual = $this->FooTable->getConfig(ConfigurationTrait::$CONFIG_OPTION_HIDDEN_ASSOCIATIONS);
        $this->assertTrue(is_array($actual), "Non-array returned from hiddenAssociations");
        $this->assertEquals($expected, $actual, "Incorrect value returned from hiddenAssociations");
    }

    public function testNotifications()
    {
        $expected = [
            'enable' => true,
            'ignored_fields' => [
                'created',
                'modified',
            ],
        ];
        $actual = $this->FooTable->getConfig(ConfigurationTrait::$CONFIG_OPTION_NOTIFICATIONS);
        $this->assertTrue(is_array($actual), "Non-array returned from notifications");
        $this->assertEquals($expected, $actual, "Incorrect value returned from notifications");
    }

    public function testIsSearchable()
    {
        $expected = true;
        $actual = $this->FooTable->isSearchable();
        $this->assertTrue(is_bool($actual), "Non-bool returned from isSearchable");
        $this->assertEquals($expected, $actual, "Incorrect value returned from isSearchable");
    }

    public function testIcon()
    {
        $expected = 'cube';
        $actual = $this->FooTable->icon();
        $this->assertTrue(is_string($actual), "Non-string returned from icon");
        $this->assertEquals($expected, $actual, "Incorrect value returned from icon");
    }

    public function testModuleAlias()
    {
        $expected = 'Foobar';
        $actual = $this->FooTable->moduleAlias();
        $this->assertTrue(is_string($actual), "Non-string returned from moduleAlias");
        $this->assertEquals($expected, $actual, "Incorrect value returned from moduleAlias");
    }

    /**
     * @dataProvider csvProvider
     */
    public function testGetFieldsDefinitions($name, $expected)
    {
        $this->assertEquals($expected, $this->FooTable->getFieldsDefinitions());
    }

    public function testModuleAliasGetter()
    {
        $this->assertSame('Foobar', $this->FooTable->moduleAlias());
    }

    public function testModuleAliasGetterDefault()
    {
        $this->FooTable->moduleAlias();
        $this->assertSame('Foobar', $this->FooTable->moduleAlias());
    }

    public function testGetReminderFields()
    {
        $fields = $this->FooTable->getReminderFields();
        $this->assertTrue(is_array($fields), "reminderFields is not an array");
        $this->assertEquals('reminder_date', $fields[0]['name'], "Field reminder is incorrectly matched");
    }

    public function testFieldsOptionsRenderer()
    {
        $fhf = new FieldHandlerFactory();
        $result = $fhf->renderValue($this->FooTable, 'status', 'active');
        $this->assertEquals('active', $result, "Field options are ignored during rendering (renderer set)");

        $result = $fhf->renderValue($this->FooTable, 'gender', 'm');
        $this->assertEquals('Male', $result, "Field options are ignored during rendering (renderer not set)");
    }

    public function csvProvider()
    {
        return [
            [
                'Foo',
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

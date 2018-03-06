<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

/**
 * CsvMigrations\Test\TestCase\Model\Table\FooTable Test Case
 */
class FooTableTest extends TestCase
{
    public $fixtures = ['plugin.CsvMigrations.Foo'];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Configure::write('CsvMigrations.modules.path', TESTS . 'config' . DS . 'Modules' . DS);

        $config = TableRegistry::exists('Foo') ? [] : ['className' => 'CsvMigrations\Test\App\Model\Table\FooTable'];
        $this->table = TableRegistry::get('Foo', $config);
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

    public function testSetCurrentUser()
    {
        $user = ['id' => 123, 'username' => 'some_foo_user'];
        $result = $this->table->setCurrentUser($user);
        $this->assertEquals($user, $result, "setCurrentUser did not return the correct user");

        $result = $this->table->getCurrentUser();
        $this->assertEquals($user, $result, "getCurrentUser did not return the correct user");
    }

    public function testGetParentRedirectUrl()
    {
        $result = $this->table->getParentRedirectUrl($this->table, $this->table->find()->first());
        $this->assertTrue(is_array($result));
    }

    /**
     * @dataProvider csvProvider
     */
    public function testGetFieldsDefinitions($name, $expected)
    {
        $this->assertEquals($expected, $this->table->getFieldsDefinitions());
    }

    public function testFieldsOptionsRenderer()
    {
        $fhf = new FieldHandlerFactory();
        $result = $fhf->renderValue($this->table, 'status', 'active');
        $this->assertEquals('active', $result, "Field options are ignored during rendering (renderer set)");

        $result = $fhf->renderValue($this->table, 'gender', 'm');
        $this->assertEquals('Male', $result, "Field options are ignored during rendering (renderer not set)");
    }

    public function csvProvider()
    {
        return [
            [
                'Foo',
                [
                    'id' => ['name' => 'id', 'type' => 'uuid', 'required' => '', 'non-searchable' => '', 'unique' => false],
                    'description' => ['name' => 'description', 'type' => 'text', 'required' => '', 'non-searchable' => true, 'unique' => false],
                    'name' => ['name' => 'name', 'type' => 'string', 'required' => '1', 'non-searchable' => '', 'unique' => true],
                    'status' => ['name' => 'status', 'type' => 'list(foo_statuses)', 'required' => '1', 'non-searchable' => '', 'unique' => false],
                    'type' => ['name' => 'type', 'type' => 'list(foo_types)', 'required' => '1', 'non-searchable' => '', 'unique' => false],
                    'gender' => ['name' => 'gender', 'type' => 'list(genders)', 'required' => '', 'non-searchable' => '', 'unique' => false],
                    'city' => ['name' => 'city', 'type' => 'list(cities)', 'required' => '', 'non-searchable' => '', 'unique' => false],
                    'country' => ['name' => 'country', 'type' => 'list(countries)', 'required' => '', 'non-searchable' => '', 'unique' => false],
                    'cost' => ['name' => 'cost', 'type' => 'money(currencies)', 'required' => '', 'non-searchable' => '', 'unique' => false],
                    'birthdate' => ['name' => 'birthdate', 'type' => 'date', 'required' => '', 'non-searchable' => '', 'unique' => false],
                    'reminder_date' => ['name' => 'reminder_date', 'type' => 'reminder', 'required' => '', 'non-searchable' => '', 'unique' => false],
                    'created' => ['name' => 'created', 'type' => 'datetime', 'required' => '', 'non-searchable' => '', 'unique' => false],
                    'modified' => ['name' => 'modified', 'type' => 'datetime', 'required' => '', 'non-searchable' => '', 'unique' => false],
                    'garden_area' => ['name' => 'garden_area', 'type' => 'metric(units_area)', 'required' => '', 'non-searchable' => '', 'unique' => false],
                    'is_primary' => ['name' => 'is_primary', 'type' => 'boolean', 'required' => '', 'non-searchable' => '', 'unique' => false],
                    'start_time' => ['name' => 'start_time', 'type' => 'time', 'required' => '', 'non-searchable' => '', 'unique' => false],
                    'balance' => ['name' => 'balance', 'type' => 'decimal(12.4)', 'required' => '', 'non-searchable' => '', 'unique' => false]
                ]
            ]
        ];
    }
}

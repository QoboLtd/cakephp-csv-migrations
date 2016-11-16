<?php
namespace CsvMigrations\Test\TestCase;

use Cake\Core\Configure;
use Cake\Event\Event;
use CsvMigrations\MigrationTrait;
use PHPUnit_Framework_TestCase;

class MigrationTraitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->mock = $this->getMockForTrait(MigrationTrait::class);

        $dir = dirname(__DIR__) . DS . 'data' . DS . 'CsvMigrations' . DS . 'migrations' . DS;
        Configure::write('CsvMigrations.migrations.path', $dir);
    }


    public function testGetReports()
    {
        $dir = dirname(__DIR__) . DS . 'data' . DS . 'CsvMigrations' . DS . 'migrations' . DS;

        Configure::write('CsvMigrations.migrations.path', $dir);
        Configure::write('CsvMigrations.reports.filename', 'reports');

        $result = $this->mock->_getReports();

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('Foo', $result);
    }

    /**
     * @dataProvider csvProvider
     */
    public function testGetFieldsDefinitions($name, $expected)
    {
        $this->assertEquals($expected, $this->mock->getFieldsDefinitions($name));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetFieldsDefinitionsThrowsException()
    {
        $this->mock->getFieldsDefinitions();
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

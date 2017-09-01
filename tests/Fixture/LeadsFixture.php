<?php
namespace CsvMigrations\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class LeadsFixture extends TestFixture
{
    public $table = 'leads';

    // Optional. Set this property to load fixtures to a different test datasource
    public $connection = 'test';

    public $fields = [
        'id' => ['type' => 'uuid'],
        'name' => ['type' => 'string', 'length' => 100, 'null' => false],
        'follow_up_date' => ['type' => 'datetime', 'null' => true],
        'assigned_to' => ['type' => 'string', 'length' => 36, 'null' => true],
        'created' => ['type' => 'datetime', 'null' => true],
        'modified' => ['type' => 'datetime', 'null' => true],
        'trashed' => ['type' => 'datetime', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'unique' => ['type' => 'unique', 'columns' => ['name', 'id']]
        ]
    ];

    public $records = [
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'name' => 'Foo',
            'follow_up_date' => '2017-08-11 11:00:00',
            'assigned_to' => '00000000-0000-0000-0000-000000000001',
            'trashed' => null,
            'created' => '2016-07-01 10:39:23',
            'modified' => '2016-07-01 10:41:31'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'Bar',
            'follow_up_date' => null,
            'assigned_to' => null,
            'trashed' => null,
            'created' => '2016-07-02 10:39:23',
            'modified' => '2016-07-02 10:41:31'
        ]
    ];
}

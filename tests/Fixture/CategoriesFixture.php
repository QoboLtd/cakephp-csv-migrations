<?php
namespace CsvMigrations\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CategoriesFixture extends TestFixture
{
    public $table = 'categories';

    // Optional. Set this property to load fixtures to a different test datasource
    public $connection = 'test';

    public $fields = [
        'id' => ['type' => 'uuid'],
        'name' => ['type' => 'string', 'length' => 100, 'null' => false],
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
            'name' => 'First Category',
            'trashed' => null,
            'created' => '2016-07-01 10:39:23',
            'modified' => '2016-07-01 10:41:31'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'Second Category',
            'trashed' => null,
            'created' => '2016-07-02 10:39:23',
            'modified' => '2016-07-02 10:41:31'
        ]
    ];
}

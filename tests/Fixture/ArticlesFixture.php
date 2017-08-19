<?php
namespace CsvMigrations\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesFixture extends TestFixture
{
    public $table = 'articles';

    // Optional. Set this property to load fixtures to a different test datasource
    public $connection = 'test';

    public $fields = [
        'id' => ['type' => 'uuid'],
        'name' => ['type' => 'string', 'length' => 100, 'null' => false],
        'category' => ['type' => 'string', 'length' => 36, 'null' => true],
        'author' => ['type' => 'string', 'length' => 36, 'null' => true],
        'status' => ['type' => 'string', 'length' => 100, 'null' => true],
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
            'category' => '00000000-0000-0000-0000-000000000002',
            'author' => '00000000-0000-0000-0000-000000000001',
            'trashed' => null,
            'status' => 'draft',
            'created' => '2016-07-01 10:39:23',
            'modified' => '2016-07-01 10:41:31'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'Bar',
            'category' => '00000000-0000-0000-0000-000000000001',
            'author' => '00000000-0000-0000-0000-000000000002',
            'trashed' => null,
            'status' => 'published',
            'created' => '2016-07-02 10:39:23',
            'modified' => '2016-07-02 10:41:31'
        ]
    ];
}

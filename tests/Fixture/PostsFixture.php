<?php
namespace CsvMigrations\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class PostsFixture extends TestFixture
{
    public $table = 'posts';

    // Optional. Set this property to load fixtures to a different test datasource
    public $connection = 'test';

    public $fields = [
        'id' => ['type' => 'uuid'],
        'name' => ['type' => 'string', 'length' => 100, 'null' => false],
        'description' => ['type' => 'text', 'length' => null, 'null' => true],
        'owner' => ['type' => 'string', 'length' => 36, 'null' => true],
        'trashed' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'unique' => ['type' => 'unique', 'columns' => ['name', 'id']]
        ]
    ];

    public $records = [
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'name' => 'Post - 1',
            'description' => 'Post 1 Description',
            'owner' => '00000000-0000-0000-0000-000000000001',
            'trashed' => null,
            'created' => '2016-07-01 10:39:23',
            'modified' => '2016-07-01 10:41:31'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'Post - 2',
            'description' => 'Post -2 Description',
            'author' => '00000000-0000-0000-0000-000000000002',
            'trashed' => null,
            'created' => '2016-07-02 10:39:23',
            'modified' => '2016-07-02 10:41:31'
        ]
    ];
}

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
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
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
            'id' => 'd8c3ba90-c418-4e58-8cb6-b65c9095a2dc',
            'name' => 'Foo',
            'trashed' => null,
            'created' => '2016-07-01 10:39:23',
            'modified' => '2016-07-01 10:41:31'
        ],
        [
            'id' => 'de90e976-a5bb-11e6-80f5-76304dec7eb7',
            'name' => 'Bar',
            'trashed' => null,
            'created' => '2016-07-02 10:39:23',
            'modified' => '2016-07-02 10:41:31'
        ]
    ];
}

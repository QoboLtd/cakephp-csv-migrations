<?php
namespace CsvMigrations\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class SimilarArticlesFixture extends TestFixture
{
    public $table = 'similar_articles';

    // Optional. Set this property to load fixtures to a different test datasource
    public $connection = 'test';

    public $fields = [
        'id' => ['type' => 'uuid'],
        'main_article_id' => ['type' => 'uuid', 'null' => true],
        'similar_article_id' => ['type' => 'uuid', 'null' => true],
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
            'main_article_id' => '00000000-0000-0000-0000-000000000001',
            'similar_article_id' => '00000000-0000-0000-0000-000000000002',
            'trashed' => null,
            'created' => '2016-07-01 10:39:23',
            'modified' => '2016-07-01 10:41:31'
        ]
    ];
}

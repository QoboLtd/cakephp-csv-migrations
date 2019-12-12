<?php

namespace CsvMigrations\Test\Fixture;

use Burzum\FileStorage\Test\Fixture\FileStorageFixture as BaseFixture;

class FileStorageFixture extends BaseFixture
{

    public $fields = [
        'id' => ['type' => 'uuid', 'null' => false, 'default' => null, 'length' => 36],
        'user_id' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 36],
        'foreign_key' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 36],
        'model' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 64],
        'filename' => ['type' => 'string', 'null' => false, 'default' => null],
        'filesize' => ['type' => 'integer', 'null' => true, 'default' => null, 'length' => 16],
        'mime_type' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 32],
        'extension' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 5],
        'hash' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 64],
        'path' => ['type' => 'string', 'null' => true, 'default' => null],
        'adapter' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'comment' => 'Gaufrette Storage Adapter Class'],
        'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'model_field' => ['type' => 'string', 'null' => true, 'default' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public $records = [
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'user_id' => '00000000-0000-0000-0000-000000000001',
            'foreign_key' => '00000000-0000-0000-0000-000000000003',
            'model' => 'articles',
            'filename' => 'qobo.png',
            'filesize' => '1186',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'hash' => null,
            'path' => 'tests/img/qobo.png',
            'adapter' => 'Local',
            'model_field' => 'image',
            'created' => '2018-10-26 12:00:00',
            'modified' => '2012-10-26 12:00:00',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'user_id' => '00000000-0000-0000-0000-000000000001',
            'foreign_key' => '00000000-0000-0000-0000-000000000004',
            'model' => 'articles',
            'filename' => 'qobo.PNG',
            'filesize' => '1186',
            'mime_type' => 'image/png',
            'extension' => 'PNG',
            'hash' => null,
            'path' => 'tests/img/qobo.PNG',
            'adapter' => 'Local',
            'model_field' => 'image',
            'created' => '2018-10-26 12:00:00',
            'modified' => '2012-10-26 12:00:00',
        ],
    ];
}

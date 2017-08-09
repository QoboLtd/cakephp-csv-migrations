<?php
namespace CsvMigrations\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ImportResultsFixture
 *
 */
class ImportResultsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'import_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'row_number' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'model_name' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'model_id' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'status' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'status_message' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'trashed' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'import_id' => ['type' => 'unique', 'columns' => ['import_id', 'row_number'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'import_id' => '986ad64e-0aa2-497a-9f1f-79113f7e05a4',
            'row_number' => 1,
            'model_name' => 'Lorem ipsum dolor sit amet',
            'model_id' => '8aab326f-59ba-4222-9d76-dbe964a2302d',
            'status' => 'Success',
            'status_message' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
            'created' => '2017-07-31 17:10:39',
            'modified' => '2017-07-31 17:10:39',
            'trashed' => null
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'import_id' => '00000000-0000-0000-0000-000000000002',
            'row_number' => 1,
            'model_name' => 'Articles',
            'model_id' => null,
            'status' => 'Fail',
            'status_message' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
            'created' => '2017-07-31 17:10:39',
            'modified' => '2017-07-31 17:10:39',
            'trashed' => null
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'import_id' => '00000000-0000-0000-0000-000000000002',
            'row_number' => 2,
            'model_name' => 'Articles',
            'model_id' => null,
            'status' => 'Pending',
            'status_message' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
            'created' => '2017-07-31 17:10:39',
            'modified' => '2017-07-31 17:10:39',
            'trashed' => null
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000004',
            'import_id' => '00000000-0000-0000-0000-000000000002',
            'row_number' => 3,
            'model_name' => 'Articles',
            'model_id' => null,
            'status' => 'Success',
            'status_message' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
            'created' => '2017-07-31 17:10:39',
            'modified' => '2017-07-31 17:10:39',
            'trashed' => null
        ],
    ];
}

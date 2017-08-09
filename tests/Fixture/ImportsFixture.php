<?php
namespace CsvMigrations\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ImportsFixture
 *
 */
class ImportsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'filename' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'options' => ['type' => 'text', 'length' => 4294967295, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'trashed' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'model_name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'attempts' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'attempted_date' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'status' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
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
            'filename' => TESTS . 'uploads' . DS . 'imports' . DS . 'import.csv',
            'options' => null,
            'created' => '2017-08-01 11:06:06',
            'modified' => '2017-08-01 11:06:06',
            'trashed' => null,
            'model_name' => 'Articles',
            'attempts' => 1,
            'attempted_date' => '2017-08-01 11:06:06',
            'status' => 'Pending'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'filename' => TESTS . 'uploads' . DS . 'imports' . DS . 'import.csv',
            'options' => '{"fields":{"name":{"column":"Name","default":""}}}',
            'created' => '2017-08-01 11:06:06',
            'modified' => '2017-08-01 11:06:06',
            'trashed' => null,
            'model_name' => 'Articles',
            'attempts' => 1,
            'attempted_date' => '2017-08-01 11:06:06',
            'status' => 'Pending'
        ],
    ];
}

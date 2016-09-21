<?php
namespace CsvMigrations\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DblistsFixture
 *
 */
class DblistsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'UNIQUE_NAME' => ['type' => 'unique', 'columns' => ['name'], 'length' => []],
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
            'id' => '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce4',
            'name' => 'categories',
            'created' => '2016-09-06 08:31:12',
            'modified' => '2016-09-06 08:31:12'
        ],
        [
            'id' => '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce5',
            'name' => 'companies',
            'created' => '2016-09-06 08:31:12',
            'modified' => '2016-09-06 08:31:12'
        ],
    ];
}

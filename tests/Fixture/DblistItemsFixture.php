<?php
namespace CsvMigrations\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DblistItemsFixture
 *
 */
class DblistItemsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'dblist_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'value' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'parent_id' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'lft' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'rght' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'UNIQUE_INDEX' => ['type' => 'unique', 'columns' => ['dblist_id', 'name', 'value'], 'length' => []],
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
            'id' => '8233ddc0-5b8a-47e6-9432-e90fcba73015',
            'dblist_id' => '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce4',
            'name' => 'Corporate',
            'value' => 'corporate',
            'active' => 1,
            'parent_id' => null,
            'lft' => 1,
            'rght' => 1,
            'created' => '2016-09-21 10:13:21',
            'modified' => '2016-09-21 10:13:21'
        ],
        [
            'id' => '8233ddc0-5b8a-47e6-9432-e90fcba73014',
            'dblist_id' => '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce4',
            'name' => 'Individual',
            'value' => 'individual',
            'active' => 1,
            'parent_id' => null,
            'lft' => 1,
            'rght' => 1,
            'created' => '2016-09-21 10:13:21',
            'modified' => '2016-09-21 10:13:21'
        ],
        [
            'id' => '8233ddc0-5b8a-47e6-9432-e90fcba73024',
            'dblist_id' => '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce4',
            'name' => 'Antonis',
            'value' => 'antonis',
            'active' => 1,
            'parent_id' => '8233ddc0-5b8a-47e6-9432-e90fcba73014',
            'lft' => 1,
            'rght' => 1,
            'created' => '2016-09-21 10:13:21',
            'modified' => '2016-09-21 10:13:21'
        ],
        [
            'id' => '8233ddc0-5b8a-47e6-9432-e90fcba73034',
            'dblist_id' => '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce4',
            'name' => 'George',
            'value' => 'george',
            'active' => 1,
            'parent_id' => '8233ddc0-5b8a-47e6-9432-e90fcba73014',
            'lft' => 1,
            'rght' => 1,
            'created' => '2016-09-21 10:13:21',
            'modified' => '2016-09-21 10:13:21'
        ],
        [
            'id' => '8233ddc0-5b8a-47e6-9432-e90fcba73044',
            'dblist_id' => '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce4',
            'name' => 'Leonid',
            'value' => 'leonid',
            'active' => 1,
            'parent_id' => '8233ddc0-5b8a-47e6-9432-e90fcba73015',
            'lft' => 1,
            'rght' => 1,
            'created' => '2016-09-21 10:13:21',
            'modified' => '2016-09-21 10:13:21'
        ],
        [
            'id' => '8233ddc0-5b8a-47e6-9432-e90fcba73005',
            'dblist_id' => '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce5',
            'name' => 'Qobo',
            'value' => 'qobo',
            'active' => 1,
            'parent_id' => null,
            'lft' => 1,
            'rght' => 1,
            'created' => '2016-09-21 10:13:21',
            'modified' => '2016-09-21 10:13:21'
        ],
        [
            'id' => '8233ddc0-5b8a-47e6-9432-e90fcba73004',
            'dblist_id' => '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce5',
            'name' => 'CakePHP',
            'value' => 'cakephp',
            'active' => 1,
            'parent_id' => null,
            'lft' => 1,
            'rght' => 1,
            'created' => '2016-09-21 10:13:21',
            'modified' => '2016-09-21 10:13:21'
        ],
    ];
}

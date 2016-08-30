<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Model\Table\DbListsTable;

/**
 * CsvMigrations\Model\Table\DbListsTable Test Case
 */
class DbListsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \CsvMigrations\Model\Table\DbListsTable
     */
    public $DbLists;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.csv_migrations.db_lists',
        'plugin.csv_migrations.db_list_items'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('DbLists') ? [] : ['className' => 'CsvMigrations\Model\Table\DbListsTable'];
        $this->DbLists = TableRegistry::get('DbLists', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->DbLists);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}

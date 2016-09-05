<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Model\Table\DbListItemsTable;

/**
 * CsvMigrations\Model\Table\DbListItemsTable Test Case
 */
class DbListItemsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \CsvMigrations\Model\Table\DbListItemsTable
     */
    public $DbListItems;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.csv_migrations.db_list_items',
        'plugin.csv_migrations.db_lists'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('DbListItems') ? [] : ['className' => 'CsvMigrations\Model\Table\DbListItemsTable'];
        $this->DbListItems = TableRegistry::get('DbListItems', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->DbListItems);

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

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}

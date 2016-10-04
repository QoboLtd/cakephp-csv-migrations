<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Model\Table\DblistItemsTable;

/**
 * CsvMigrations\Model\Table\DblistItemsTable Test Case
 */
class DblistItemsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \CsvMigrations\Model\Table\DblistItemsTable
     */
    public $DblistItems;


    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('DblistItems') ? [] : ['className' => 'CsvMigrations\Model\Table\DblistItemsTable'];
        $this->DblistItems = TableRegistry::get('DblistItems', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->DblistItems);

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

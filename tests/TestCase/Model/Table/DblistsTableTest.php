<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Model\Table\DblistsTable;

/**
 * CsvMigrations\Model\Table\DblistsTable Test Case
 */
class DblistsTableTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CsvMigrations.Dblists',
        'plugin.CsvMigrations.DblistItems',
    ];

    /**
     * Test subject
     *
     * @var \CsvMigrations\Model\Table\DblistsTable
     */
    public $Dblists;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Dblists') ? [] : ['className' => 'CsvMigrations\Model\Table\DblistsTable'];
        $this->Dblists = TableRegistry::get('Dblists', $config);
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->assertTrue($this->Dblists->hasBehavior('Timestamp'), 'Missing behavior Timestamp.');
        $assoc = $this->Dblists->association('DblistItems');
        $this->assertFalse(is_null($assoc), 'DblistItems cannot be found');
        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $assoc, 'Dblists\'s association with DblistItems should be hasMany');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $validator = $this->Dblists->validator();
        $this->assertTrue($validator->hasField('id'), 'Missing validation for id');
        $this->assertTrue($validator->hasField('name'), 'Missing validation for name');
    }

    /**
     * Test Options query.
     *
     * @return void
     */
    public function testOptions()
    {
        $list = 'categories';
        $query = $this->Dblists->find('options', ['name' => $list]);
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertFalse($query->isEmpty());

        $list = null;
        $result = $this->Dblists->find('options', ['name' => $list]);
        $this->assertTrue(is_array($result), 'Return shouold be an empty array on wrong name lists');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Dblists);

        parent::tearDown();
    }
}

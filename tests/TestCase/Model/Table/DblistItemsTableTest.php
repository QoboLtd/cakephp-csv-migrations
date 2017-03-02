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
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $displayField = $this->DblistItems->displayField();
        $this->assertEquals('name', $displayField, 'Display field is the name');
        $pK = $this->DblistItems->primaryKey();
        $this->assertEquals('id', $pK, 'Primary key is the id');
        $this->assertTrue($this->DblistItems->hasBehavior('Timestamp'), 'Missing behavior Timestamp');
        $this->assertTrue($this->DblistItems->hasBehavior('Tree'), 'Missing behavior Tree');
        $assoc = $this->DblistItems->association('Dblists');
        $this->assertFalse(is_null($assoc), 'DblistItems cannot be found');
        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $assoc, 'DblistItems\'s association with Dblists should be belongsTo');
    }

    /**
     * Test validationDefault method
     * @dataProvider testValidationDefaultProvider
     *
     * @return void
     */
    public function testValidationDefault($fieldName)
    {
        $validator = $this->DblistItems->validator();
        $this->assertTrue($validator->hasField($fieldName), 'Missing validation for ' . $fieldName);
    }

    /**
     * Data provider of testValidationDefault
     *
     * @return array Field names
     */
    public function testValidationDefaultProvider()
    {
        return [
            ['id'],
            ['name'],
            ['value'],
        ];
    }

    /**
     * Test Options query.
     *
     * @return void
     */
    public function testTreeEntities()
    {
        $id = '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce4';
        $query = $this->DblistItems->find('treeEntities', ['listId' => $id]);
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertFalse($query->isEmpty(), 'List with items, then all items should be returned');
        foreach ($query as $item) {
            if ($item->parent_id) {
                $this->assertNotEmpty($item->get('spacer'), 'Spacer must be part of each entity');
                $spacer = '&nbsp;&nbsp;&nbsp;&nbsp;';
                $this->assertContains($spacer, $item->get('spacer'), 'Spacer field should contain ' . $spacer);
                break;
            }
        }
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
}

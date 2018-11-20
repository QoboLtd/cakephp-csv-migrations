<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

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

    public $DblistItems;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp() : void
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
    public function testInitialize() : void
    {
        $displayField = $this->DblistItems->getDisplayField();
        $this->assertEquals('name', $displayField, 'Display field is the name');
        $pK = $this->DblistItems->getPrimaryKey();
        $this->assertEquals('id', $pK, 'Primary key is the id');
        $this->assertTrue($this->DblistItems->hasBehavior('Timestamp'), 'Missing behavior Timestamp');
        $this->assertTrue($this->DblistItems->hasBehavior('Tree'), 'Missing behavior Tree');
        $assoc = $this->DblistItems->getAssociation('Dblists');
        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $assoc, 'DblistItems\'s association with Dblists should be belongsTo');
    }

    /**
     * Test validationDefault method
     * @dataProvider validationDefaultProvider
     *
     * @return void
     */
    public function testValidationDefault(string $fieldName) : void
    {
        $validator = $this->DblistItems->getValidator();
        $this->assertTrue($validator->hasField($fieldName), 'Missing validation for ' . $fieldName);
    }

    /**
     * Data provider of testValidationDefault
     *
     * @return mixed[] Field names
     */
    public function validationDefaultProvider() : array
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
    public function testTreeEntities() : void
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
    public function tearDown() : void
    {
        unset($this->DblistItems);

        parent::tearDown();
    }
}

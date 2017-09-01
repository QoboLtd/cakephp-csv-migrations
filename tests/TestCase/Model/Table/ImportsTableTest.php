<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Model\Table\ImportsTable;

/**
 * CsvMigrations\Model\Table\ImportsTable Test Case
 */
class ImportsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \CsvMigrations\Model\Table\ImportsTable
     */
    public $Imports;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.csv_migrations.imports',
        'plugin.csv_migrations.import_results'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Imports') ? [] : ['className' => ImportsTable::class];
        $this->Imports = TableRegistry::get('Imports', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Imports);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->assertTrue($this->Imports->hasBehavior('Timestamp'), 'Missing behavior Timestamp.');
        $this->assertTrue($this->Imports->hasBehavior('Trash'), 'Missing behavior Trash.');
        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->Imports->association('ImportResults'));
        $this->assertInstanceOf(ImportsTable::class, $this->Imports);
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $validator = new \Cake\Validation\Validator();
        $result = $this->Imports->validationDefault($validator);

        $this->assertInstanceOf('\Cake\Validation\Validator', $result);
    }
}

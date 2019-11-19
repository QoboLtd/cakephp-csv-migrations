<?php

namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Model\Table\ImportResultsTable;

/**
 * CsvMigrations\Model\Table\ImportResultsTable Test Case
 */
class ImportResultsTableTest extends TestCase
{
    public $ImportResults;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.csv_migrations.import_results',
        'plugin.csv_migrations.imports'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::exists('ImportResults') ? [] : ['className' => ImportResultsTable::class];

        $this->ImportResults = TableRegistry::get('ImportResults', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ImportResults);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertTrue($this->ImportResults->hasBehavior('Timestamp'), 'Missing behavior Timestamp.');
        $this->assertTrue($this->ImportResults->hasBehavior('Trash'), 'Missing behavior Trash.');
        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->ImportResults->getAssociation('Imports'));
        $this->assertInstanceOf(ImportResultsTable::class, $this->ImportResults);
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $validator = new \Cake\Validation\Validator();
        $result = $this->ImportResults->validationDefault($validator);

        $this->assertInstanceOf('\Cake\Validation\Validator', $result);
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $rules = new \Cake\ORM\RulesChecker();
        $result = $this->ImportResults->buildRules($rules);

        $this->assertInstanceOf('\Cake\ORM\RulesChecker', $result);
    }
}

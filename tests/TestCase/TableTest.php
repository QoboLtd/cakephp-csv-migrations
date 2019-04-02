<?php
namespace CsvMigrations\Test\TestCase;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;

use CsvMigrations\Test\App\Model\Table\FooTable;

/**
 * CsvMigrations\Table Test Case
 */
class TableTest extends TestCase
{
    /**
     * Foo table.
     * @var \CsvMigrations\Test\App\Model\Table\FooTable
     */
    public $Table;

    /**
     * Default table validation value
     * @var bool
     */
    protected $defaultTableValidation;

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
    public function setUp() : void
    {
        parent::setUp();

        $this->defaultTableValidation = Configure::readOrFail('CsvMigrations.tableValidation');

        Configure::write('CsvMigrations.modules.path', TESTS . 'config' . DS . 'Modules' . DS);
        $config = TableRegistry::getTableLocator()->exists('Foo') ? [] : ['className' => FooTable::class];
        /** @var \CsvMigrations\Test\App\Model\Table\FooTable $table */
        $table = TableRegistry::getTableLocator()->get('Foo', $config);
        $this->Table = $table;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown() : void
    {
        Configure::write('CsvMigrations.tableValidation', $this->defaultTableValidation);
        unset($this->defaultTableValidation);
        unset($this->Table);

        parent::tearDown();
    }

    /**
     * Test validationDefault method when table validation is disabled
     *
     * @return void
     */
    public function testValidationDefaultTableValidationDisabled() : void
    {
        Configure::write('CsvMigrations.tableValidation', false);
        $validator = new Validator();
        $expected = clone $validator;
        $validator = $this->Table->validationDefault($validator);

        $this->assertInstanceOf(Validator::class, $validator);
        $this->assertEquals($expected, $validator, 'Validation class has been modified by table validation.');
    }

    /**
     * Test validationDefault method when table validation is enabled
     *
     * @return void
     */
    public function testValidationDefaultTableValidationEnabled() : void
    {
        Configure::write('CsvMigrations.tableValidation', true);
        $validator = new Validator();
        $expected = clone $validator;
        $validator = $this->Table->validationDefault($validator);

        $this->assertInstanceOf(Validator::class, $validator);
        $this->assertNotEquals($expected, $validator, 'Validation class was not modified by table validation rules.');
    }
}

<?php
namespace CsvMigrations\Test\TestCase;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldTrait;

class FieldTraitTest extends TestCase
{
    /**
     * FooTable instance
     *
     * @var CsvMigrations\Test\TestCase\Model\Table\FooTable
     */
    public $FooTable;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $dir = dirname(__DIR__) . DS . 'data' . DS . 'CsvMigrations' . DS;

        /*
        point to test data
         */
        Configure::write('CsvMigrations.migrations.path', $dir . 'migrations' . DS);
        Configure::write('CsvMigrations.lists.path', $dir . 'lists' . DS);
        Configure::write('CsvMigrations.migrations.filename', 'migration');
        $config = TableRegistry::exists('Foo') ? [] : ['className' => 'CsvMigrations\Test\TestCase\Model\Table\FooTable'];
        $this->FooTable = TableRegistry::get('Foo', $config);
        $this->mock = $this->getMockForTrait(FieldTrait::class);
    }

    public function testGetUniqueFields()
    {
        $uniqueFields = $this->mock->getUniqueFields($this->FooTable);
        sort($uniqueFields);
        $this->assertEquals(['id', 'name'], $uniqueFields);
    }
}

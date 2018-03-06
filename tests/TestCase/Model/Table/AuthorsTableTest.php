<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\Table;

class AuthorsTableTest extends TestCase
{
    public $fixtures = [
        'plugin.csv_migrations.authors',
        'plugin.csv_migrations.posts',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Configure::write('CsvMigrations.modules.path', TESTS . 'config' . DS . 'Modules' . DS);

        $config = TableRegistry::exists('Authors') ? [] : ['className' => 'CsvMigrations\Test\TestCase\Model\Table\AuthorsTable'];
        $this->table = TableRegistry::get('Authors', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->table);

        parent::tearDown();
    }
}

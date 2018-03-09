<?php
namespace CsvMigrations\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class PostsTableTest extends TestCase
{
    public $fixtures = [
        'plugin.csv_migrations.posts',
        'plugin.csv_migrations.authors',
        'plugin.csv_migrations.tags',
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

        $config = TableRegistry::exists('Posts') ? [] : ['className' => 'CsvMigrations\Test\App\Model\Table\PostsTable'];
        $this->table = TableRegistry::get('Posts', $config);
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

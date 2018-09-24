<?php
namespace CsvMigrations\Test\TestCase\Shell;

use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\ConsoleOutput;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestCase;
use CsvMigrations\Shell\ImportShell;

/**
 * CsvMigrations\Shell\ImportShell Test Case
 */
class ImportShellTest extends ConsoleIntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.csv_migrations.articles',
        'plugin.csv_migrations.authors',
        'plugin.csv_migrations.imports',
        'plugin.csv_migrations.import_results'
    ];

    /**
     * ConsoleIo mock
     *
     * @var \Cake\Console\ConsoleIo|\PHPUnit_Framework_MockObject_MockObject
     */
    public $io;

    /**
     * Test subject
     *
     * @var \CsvMigrations\Shell\ImportShell
     */
    public $ImportShell;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $io = new ConsoleIo(new ConsoleOutput());
        $io->level(ConsoleIo::QUIET);
        $this->ImportShell = new ImportShell($io);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->ImportShell);

        parent::tearDown();
    }

    /**
     * Test getOptionParser method
     *
     * @return void
     */
    public function testGetOptionParser()
    {
        $parser = $this->ImportShell->getOptionParser();

        $this->assertInstanceOf(ConsoleOptionParser::class, $parser);
        $this->assertEquals('Process all import jobs', $parser->getDescription());
    }

    /**
     * Test main method
     *
     * @return void
     */
    public function testMain()
    {
        $table = TableRegistry::getTableLocator()->get('Articles');
        $initialCount = $table->find()->count();

        $this->ImportShell->main();

        $count = $table->find()->count();
        $this->assertNotEquals($initialCount, $count);
        $this->assertGreaterThan($initialCount, $count);
        $this->assertEquals(4, $count);

        $this->assertEquals(
            ['name' => 'John Doe [import]', 'author' => '00000000-0000-0000-0000-000000000001', 'status' => 'draft'],
            $table->findByName('John Doe [import]')->select(['name', 'author', 'status'])->first()->toArray()
        );

        $this->assertEquals(
            ['name' => 'John Smith [import]', 'author' => '00000000-0000-0000-0000-000000000002', 'status' => 'published'],
            $table->findByName('John Smith [import]')->select(['name', 'author', 'status'])->first()->toArray()
        );
    }
}

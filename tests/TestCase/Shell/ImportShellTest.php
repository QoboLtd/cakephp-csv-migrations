<?php
namespace CsvMigrations\Test\TestCase\Shell;

use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\ConsoleOutput;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestCase;
use CsvMigrations\Shell\ImportShell;
use Webmozart\Assert\Assert;

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
        'plugin.CakeDC/Users.users',
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
    public function setUp() : void
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
    public function tearDown() : void
    {
        unset($this->ImportShell);

        parent::tearDown();
    }

    /**
     * Test getOptionParser method
     *
     * @return void
     */
    public function testGetOptionParser() : void
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
    public function testMain() : void
    {
        $table = TableRegistry::getTableLocator()->get('Articles');
        $initialCount = $table->find()->count();

        $this->ImportShell->main();

        $this->assertSame($initialCount + 2, $table->find()->count());

        $entity = $table->find()
            ->where(['name' => 'John Doe [import]'])
            ->select(['name', 'author', 'status'])
            ->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);

        $this->assertEquals(
            ['name' => 'John Doe [import]', 'author' => '00000000-0000-0000-0000-000000000001', 'status' => 'draft'],
            $entity->toArray()
        );

        $entity = $table->find()
            ->where(['name' => 'John Smith [import]'])
            ->select(['name', 'author', 'status'])
            ->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);

        $this->assertEquals(
            ['name' => 'John Smith [import]', 'author' => '00000000-0000-0000-0000-000000000002', 'status' => 'published'],
            $entity->toArray()
        );
    }
}

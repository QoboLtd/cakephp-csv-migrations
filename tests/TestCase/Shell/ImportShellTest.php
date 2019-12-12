<?php

namespace CsvMigrations\Test\TestCase\Shell;

use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\ConsoleOutput;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestCase;
use CsvMigrations\Model\Table\ImportResultsTable;
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
        'plugin.csv_migrations.import_results',
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
    public function setUp(): void
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
    public function tearDown(): void
    {
        unset($this->ImportShell);

        parent::tearDown();
    }

    /**
     * Test getOptionParser method
     *
     * @return void
     */
    public function testGetOptionParser(): void
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
    public function testMain(): void
    {
        $table = TableRegistry::getTableLocator()->get('Articles');
        $initialCount = $table->find()->count();

        $this->ImportShell->main();

        $this->assertSame($initialCount + 5, $table->find()->count());

        $check = [
            [
                'name' => 'John Doe [import]',
                'author' => '00000000-0000-0000-0000-000000000001',
                'status' => 'draft',
                'featured' => true,
                'date' => (new \Cake\I18n\Date('2019-06-30')),
            ],
            [
                'name' => 'John Smith [import]',
                'author' => '00000000-0000-0000-0000-000000000002',
                'status' => 'published',
                'featured' => false,
                'date' => (new \Cake\I18n\Date('2019-06-05')),
            ],
            [
                'name' => 'Michael Cain [import]',
                'author' => '00000000-0000-0000-0000-000000000001',
                'status' => 'draft',
                'featured' => false,
                'date' => (new \Cake\I18n\Date('2019-04-13')),
            ],
            [
                'name' => 'John Kemp [import]',
                'author' => '00000000-0000-0000-0000-000000000001',
                'status' => 'draft',
                'featured' => true,
                'date' => (new \Cake\I18n\Date('2019-06-22')),
            ],
            [
                'name' => 'Michael Johnson [import]',
                'author' => '00000000-0000-0000-0000-000000000002',
                'status' => 'published',
                'featured' => true,
                'date' => (new \Cake\I18n\Date('2019-02-03')),
            ],
        ];

        foreach ($check as $expected) {
            $entity = $table->find()
                ->where(['name' => $expected['name']])
                ->select(['name', 'author', 'status', 'featured', 'date'])
                ->firstOrFail();

            Assert::isInstanceOf($entity, EntityInterface::class);

            $this->assertEquals($expected, $entity->toArray());
        }
    }

    /**
     * Test main method with invalid dates
     *
     * @return void
     */
    public function testMainWithInvalidDates(): void
    {
        TableRegistry::getTableLocator()->get('CsvMigrations.ImportResults')->deleteAll([]);

        $table = TableRegistry::getTableLocator()->get('CsvMigrations.Imports');
        $table->deleteAll([]);

        $entity = $table->newEntity([
            'filename' => TESTS . 'uploads' . DS . 'imports' . DS . 'articles-with-invalid-dates.csv',
            'options' => [
                'fields' => [
                    'name' => ['column' => 'Name', 'default' => ''],
                    'date' => ['column' => 'Date', 'default' => ''],
                ],
            ],
            'model_name' => 'Articles',
            'attempts' => 1,
            'status' => 'Pending',
            'created_by' => '00000000-0000-0000-0000-000000000001',
            'modified_by' => '00000000-0000-0000-0000-000000000002',
        ]);
        $table->save($entity);

        $table = TableRegistry::getTableLocator()->get('Articles');
        $initialCount = $table->find()->count();

        $this->ImportShell->main();

        $this->assertSame($initialCount, $table->find()->count());

        $query = $table->find()
            ->where(['name IN' => ['Foo [import]', 'Bar [import]']])
            ->select(['id']);

        $this->assertTrue($query->isEmpty());

        $table = TableRegistry::getTableLocator()->get('CsvMigrations.ImportResults');
        foreach ($table->find()->all() as $entity) {
            $this->assertSame(ImportResultsTable::STATUS_FAIL, $entity->get('status'));
            $this->assertSame('Import failed: {"date":{"date":"The provided value is invalid"}}', $entity->get('status_message'));
        }
    }
}

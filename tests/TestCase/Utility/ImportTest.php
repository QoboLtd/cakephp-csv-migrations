<?php

namespace CsvMigrations\Test\TestCase\Utility;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Component\FlashComponent;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Test\App\Controller\ArticlesController;
use CsvMigrations\Utility\Import;
use Webmozart\Assert\Assert;

/**
 * CsvMigrations\Utility\Import Test Case
 */
class ImportTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.Imports',
        'plugin.CsvMigrations.ImportResults',
    ];

    private $table;
    private $import;
    private $serverRequest;
    private $flashComponent;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('CsvMigrations.ImportResults');
        $this->import = TableRegistry::getTableLocator()
            ->get('CsvMigrations.Imports')
            ->get('00000000-0000-0000-0000-000000000002');

        $this->serverRequest = new ServerRequest();
        $this->flashComponent = new FlashComponent(new ComponentRegistry(new ArticlesController()));
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->flashComponent);
        unset($this->serverRequest);
        unset($this->import);
        unset($this->table);

        parent::tearDown();
    }

    public function testGetProcessedFile(): void
    {
        $this->assertSame(
            TESTS . 'uploads' . DS . 'imports' . DS . 'articles.processed.csv',
            Import::getProcessedFile($this->import)
        );
    }

    public function testGetProcessedFileWithoutFullBase(): void
    {
        $this->assertSame('articles.processed.csv', Import::getProcessedFile($this->import, false));
    }

    public function testUploadWithoutFile(): void
    {
        $instance = new Import($this->table, $this->serverRequest, $this->flashComponent);

        $this->assertSame('', $instance->upload());
    }

    public function testUploadWithInvalidFileType(): void
    {
        $serverRequestWithInvalidFileType = $this->serverRequest->withData('file', ['type' => 'application/pdf']);

        $instance = new Import($this->table, $serverRequestWithInvalidFileType, $this->flashComponent);

        $this->assertSame('', $instance->upload());
    }

    public function testUploadWithInvalidFileName(): void
    {
        $serverRequestWithInvalidFileName = $this->serverRequest->withData('file', [
            'type' => 'application/csv',
            'name' => true,
        ]);

        $instance = new Import($this->table, $serverRequestWithInvalidFileName, $this->flashComponent);

        $this->assertSame('', $instance->upload());
    }

    public function testUploadWithInvalidFileTmpName(): void
    {
        $serverRequestWithInvalidFileTmpName = $this->serverRequest->withData('file', [
            'type' => 'application/csv',
            'name' => 'foo',
            'tmp_name' => false,
        ]);

        $instance = new Import($this->table, $serverRequestWithInvalidFileTmpName, $this->flashComponent);

        $this->assertSame('', $instance->upload());
    }

    public function testUpload(): void
    {
        $serverRequestWithFile = $this->serverRequest->withData('file', [
            'type' => 'application/csv',
            'name' => 'foo.csv',
            'tmp_name' => TESTS . 'uploads' . DS . 'imports' . DS . 'articles.csv',
        ]);

        $instance = new Import($this->table, $serverRequestWithFile, $this->flashComponent);

        $this->assertSame('', $instance->upload());
    }

    public function testCreate(): void
    {
        $serverRequestWithPluginAndController = $this->serverRequest
            ->withParam('plugin', 'Foo')
            ->withParam('controller', 'Bar');

        $instance = new Import($this->table, $serverRequestWithPluginAndController, $this->flashComponent);

        $table = TableRegistry::getTableLocator()->get('CsvMigrations.Imports');
        Assert::isInstanceOf($table, \CsvMigrations\Model\Table\ImportsTable::class);
        $result = $instance->create($table, $this->import, 'foobar');

        $this->assertTrue($result);
        $this->assertSame('foobar', $this->import->get('filename'));
        $this->assertSame('Pending', $this->import->get('status'));
        $this->assertSame('Foo.Bar', $this->import->get('model_name'));
        $this->assertSame(0, $this->import->get('attempts'));
    }

    public function testGetImportResults(): void
    {
        $instance = new Import($this->table, $this->serverRequest, $this->flashComponent);

        $this->assertCount(3, $instance->getImportResults($this->import, ['created']));
    }

    public function testGetImportResultsWithInvalidSortOrder(): void
    {
        $serverRequestWithInvalidSortOrder = $this->serverRequest->withQueryParams([
            'order' => [
                ['dir' => 'invalid-sort-order'],
            ],
        ]);

        $instance = new Import($this->table, $serverRequestWithInvalidSortOrder, $this->flashComponent);

        $this->assertCount(3, $instance->getImportResults($this->import, ['created']));
    }

    public function testPrepareOptions(): void
    {
        $options = [
            'fields' => [
                'name' => [
                    'column' => 'title',
                    'default' => 'Hello World',
                ],
                'category' => [
                    'default' => 'News',
                ],
                'status' => [
                    'column' => 'article_status',
                ],
                'author' => [],
            ],
        ];

        $expected = [
            'fields' => [
                'name' => [
                    'column' => 'title',
                    'default' => 'Hello World',
                ],
                'category' => [
                    'default' => 'News',
                ],
                'status' => [
                    'column' => 'article_status',
                ],
            ],
        ];
        $this->assertSame($expected, Import::prepareOptions($options));
    }

    public function testPrepareOptionsWithoutFields(): void
    {
        $this->assertSame([], Import::prepareOptions([]));
    }

    public function testGetRowsCount(): void
    {
        $this->assertSame(5, Import::getRowsCount($this->import->get('filename')));
        $this->assertSame(6, Import::getRowsCount($this->import->get('filename'), true));
    }

    public function testGetRowsCountWithMultilineCell(): void
    {
        $path = TESTS . 'uploads' . DS . 'imports' . DS . 'articles-with-multiline-cell.csv';

        $this->assertSame(2, Import::getRowsCount($path));
        $this->assertSame(3, Import::getRowsCount($path, true));
    }

    public function testGetUploadHeaders(): void
    {
        $this->assertSame(['Name', 'Author', 'Status', 'Featured', 'Date'], Import::getUploadHeaders($this->import));
    }

    public function testGetTableColumns(): void
    {
        $instance = new Import($this->table, $this->serverRequest, $this->flashComponent);

        $expected = [
            'import_id',
            'model_id',
            'model_name',
            'row_number',
            'status',
            'status_message',
        ];

        $result = $instance->getTableColumns();
        sort($result);

        $this->assertSame($expected, $result);
    }

    public function testToDatatables(): void
    {
        $query = $this->table->find();

        $columns = ['row_number', 'status', 'status_message'];

        $expected = [
            [1, 'Success', 'Lorem ipsum dolor sit amet, aliquet feugiat.'],
            [1, 'Fail', 'Lorem ipsum dolor sit amet, aliquet feugiat.'],
            [2, 'Pending', 'Lorem ipsum dolor sit amet, aliquet feugiat.'],
            [3, 'Pending', 'Lorem ipsum dolor sit amet, aliquet feugiat.'],
        ];
        $result = Import::toDatatables($query->all(), $columns);

        $this->assertSame($expected, $result);
    }

    public function testToDatatablesWithEmptyResultSet(): void
    {
        $this->table->deleteAll([]);
        $query = $this->table->find();
        $columns = ['row_number', 'status', 'status_message'];

        $this->assertSame([], Import::toDatatables($query->all(), $columns));
    }

    public function testActionButtons(): void
    {
        $articlesTable = TableRegistry::getTableLocator()->get('CsvMigrations.Articles');
        $query = $this->table->find();

        $columns = ['row_number', 'status', 'status_message'];
        $data = Import::toDatatables($query->all(), $columns);

        $expected = '/csv-migrations/articles/view/8aab326f-59ba-4222-9d76-dbe964a2302d';

        $result = Import::actionButtons($query->all(), $articlesTable, $data);

        $this->assertContains($expected, end($result[0]));
    }

    public function testSetStatusLabels(): void
    {
        $articlesTable = TableRegistry::getTableLocator()->get('CsvMigrations.Articles');
        $query = $this->table->find();

        $columns = ['row_number', 'status', 'status_message'];
        $data = Import::toDatatables($query->all(), $columns);

        $expected = '<span class="label label-success">Success</span>';

        $result = Import::setStatusLabels($data, 1);

        $this->assertContains($expected, $result[0][1]);
    }
}

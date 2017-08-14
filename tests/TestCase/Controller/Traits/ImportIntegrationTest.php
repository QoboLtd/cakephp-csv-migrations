<?php
namespace CsvMigrations\Test\TestCase\Controller\Traits;

use Cake\Core\Configure;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use CsvMigrations\Model\Entity\Import;
use CsvMigrations\Utility\Import as ImportUtility;

/**
 * CsvMigrations\Controller\Traits\ImportTrait Test Case
 */
class ImportIntegrationTest extends IntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.csv_migrations.articles',
        'plugin.csv_migrations.imports',
        'plugin.csv_migrations.import_results'
    ];

    public function setUp()
    {
        parent::setUp();

        $this->enableRetainFlashMessages();

        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000001']);
    }

    public function testImportGet()
    {
        $this->get('/articles/import');

        $this->assertResponseOk();

        $this->assertInstanceOf(Import::class, $this->viewVariable('import'));
        $this->assertInstanceOf(ResultSet::class, $this->viewVariable('existingImports'));

        $existingImports = $this->viewVariable('existingImports');
        $this->assertFalse($existingImports->isEmpty());
    }

    public function testImportGetExisting()
    {
        $this->get('/articles/import/00000000-0000-0000-0000-000000000001');

        $this->assertResponseOk();

        $this->assertInstanceOf(Import::class, $this->viewVariable('import'));

        $this->assertEquals(['name', 'category', 'author', 'status'], $this->viewVariable('columns'));
        $this->assertEquals(['Name'], $this->viewVariable('headers'));
    }

    public function testImportGetExistingMapped()
    {
        $this->get('/articles/import/00000000-0000-0000-0000-000000000002');

        $this->assertResponseOk();

        $this->assertInstanceOf(Import::class, $this->viewVariable('import'));

        $this->assertEquals(1, $this->viewVariable('failCount'));
        $this->assertEquals(1, $this->viewVariable('pendingCount'));
        $this->assertEquals(1, $this->viewVariable('importCount'));
    }

    public function testImportPost()
    {
        $this->markTestSkipped();

        $stub = $this->getMockBuilder(ImportUtility::class, ['moveUploadedFile'])
            ->disableOriginalConstructor()
            ->getMock();

        // Copy the file instead of 'moveUploadedFile' to allow testing
        $stub->expects($this->any())
            ->method('moveUploadedFile')
            ->will($this->returnValue('copy'));

        $this->enableRetainFlashMessages();

        $data = [
            'file' => [
                'tmp_name' => TESTS . 'uploads' . DS . 'tmp' . DS . 'import',
                'name' => 'import.csv',
                'type' => 'text/csv'
            ]
        ];
        $this->post('/articles/import', $data);

        $this->assertResponseOk();
        $this->assertSession('Please choose a file to upload', 'Flash.flash.0.message');
    }

    public function testImportPostWithoutFile()
    {
        $data = [];
        $this->post('/articles/import', $data);

        $this->assertResponseOk();
        $this->assertSession('Please choose a file to upload.', 'Flash.flash.0.message');
    }

    public function testImportPostInvalidFile()
    {
        $data = [
            'file' => [
                'type' => 'unsupported_file_type'
            ]
        ];
        $this->post('/articles/import', $data);

        $this->assertResponseOk();
        $this->assertSession('Unable to upload file, unsupported file provided.', 'Flash.flash.0.message');
    }

    public function testImportPut()
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $data = [
            'options' => [
                'fields' => [
                    'name' => [
                        'column' => 'Name',
                        'default' => ''
                    ]
                ]
            ]
        ];
        $this->put('/articles/import/' . $id, $data);

        $this->assertRedirect();

        $table = TableRegistry::get('CsvMigrations.Imports');
        $entity = $table->get($id);

        $this->assertEquals($data['options'], $entity->get('options'));
    }
}

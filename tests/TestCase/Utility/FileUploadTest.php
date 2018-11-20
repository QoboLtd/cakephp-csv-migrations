<?php
namespace CsvMigrations\Test\TestCase\Utility;

use Burzum\FileStorage\Model\Entity\FileStorage;
use Burzum\FileStorage\Storage\Listener\LocalListener;
use Burzum\FileStorage\Storage\StorageManager;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Utility\FileUpload;

/**
 * CsvMigrations\Utility\Import Test Case
 */
class FileUploadTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.file_storage'
    ];

    private $table;
    private $fileUpload;

    public function setUp() : void
    {
        parent::setUp();
        StorageManager::setConfig('Local', [
            'adapterOptions' => [TMP, true],
            'adapterClass' => '\Gaufrette\Adapter\Local',
            'class' => '\Gaufrette\Filesystem'
        ]);

        // @link https://github.com/burzum/cakephp-file-storage/blob/master/docs/Documentation/Included-Event-Listeners.md
        EventManager::instance()->on(new LocalListener([
            'imageProcessing' => true,
            'pathBuilderOptions' => [
                'pathPrefix' => '/uploads'
            ]
        ]));

        $this->table = TableRegistry::get('Burzum/FileStorage.FileStorage');
        $this->fileUpload = new FileUpload(TableRegistry::get('Articles'));
    }

    public function tearDown() : void
    {
        unset($this->fileUpload);
        unset($this->table);

        parent::tearDown();
    }

    public function testSave() : void
    {
        $data = [
            'tmp_name' => TESTS . 'img' . DS . 'qobo.png',
            'error' => 0,
            'name' => 'qobo.png',
            'type' => 'image/png',
            'size' => 1186
        ];

        $result = $this->fileUpload->save('image', $data);

        $this->assertInstanceOf(FileStorage::class, $result);
        $this->assertTrue(file_exists(TMP . $result->get('path')));
    }

    public function testSaveAll() : void
    {
        $data = [
            [
                'tmp_name' => TESTS . 'img' . DS . 'qobo.png',
                'error' => 0,
                'name' => 'qobo.png',
                'type' => 'image/png',
                'size' => 1186
            ],
            [
                'tmp_name' => TESTS . 'img' . DS . 'qobo.png',
                'error' => 0,
                'name' => 'qobo.png',
                'type' => 'image/png',
                'size' => 1186
            ]
        ];

        $result = $this->fileUpload->saveAll('image', $data);

        foreach ($result as $entity) {
            $this->assertInstanceOf(FileStorage::class, $entity);
            $this->assertTrue(file_exists(TMP . $entity->get('path')));
        }
    }

    public function testSaveWithMissingParameter() : void
    {
        $data = [
            'tmp_name' => TESTS . 'img' . DS . 'qobo.png',
            'error' => 0,
            'name' => 'qobo.png',
            'type' => 'image/png',
            // 'size' => 1186 // commented out required parameter
        ];

        $this->assertNull($this->fileUpload->save('image', $data));
    }

    public function testSaveWithError() : void
    {
        $data = [
            'tmp_name' => TESTS . 'img' . DS . 'qobo.png',
            'error' => 1,
            'name' => 'qobo.png',
            'type' => 'image/png',
            'size' => 1186
        ];

        $this->assertNull($this->fileUpload->save('image', $data));
    }

    public function testGetFiles() : void
    {
        $result = $this->fileUpload->getFiles('image', '00000000-0000-0000-0000-000000000003');

        $this->assertInstanceOf(ResultSetInterface::class, $result);
        $this->assertFalse($result->isEmpty());

        $expected = [
            'huge' => 'tests/img/qobo.png',
            'large' => 'tests/img/qobo.png',
            'medium' => 'tests/img/qobo.png',
            'small' => 'tests/img/qobo.png',
            'tiny' => 'tests/img/qobo.png'
        ];
        $this->assertSame($expected, $result->firstOrFail()->get('thumbnails'));
    }
}

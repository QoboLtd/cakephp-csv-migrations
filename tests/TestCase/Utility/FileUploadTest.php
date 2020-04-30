<?php

namespace CsvMigrations\Test\TestCase\Utility;

use Burzum\FileStorage\Model\Entity\FileStorage;
use Burzum\FileStorage\Storage\Listener\LocalListener;
use Burzum\FileStorage\Storage\StorageManager;
use Cake\Core\Configure;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Event\ProcessThumbnailsListener;
use CsvMigrations\Utility\FileUpload;

/**
 * CsvMigrations\Utility\Import Test Case
 */
class FileUploadTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.file_storage',
    ];

    private $table;
    private $fileUpload;

    public function setUp(): void
    {
        parent::setUp();

        EventManager::instance()->setEventList(new EventList());
        EventManager::instance()->on(new ProcessThumbnailsListener());

        StorageManager::config('Local', [
            'adapterOptions' => [TMP, true],
            'adapterClass' => '\Gaufrette\Adapter\Local',
            'class' => '\Gaufrette\Filesystem',
        ]);

        // @link https://github.com/burzum/cakephp-file-storage/blob/master/docs/Documentation/Included-Event-Listeners.md
        EventManager::instance()->on(new LocalListener([
            'imageProcessing' => true,
            'pathBuilderOptions' => [
                'pathPrefix' => '/uploads',
            ],
        ]));

        $this->table = TableRegistry::getTableLocator()->get('Burzum/FileStorage.FileStorage');
        $this->fileUpload = new FileUpload(TableRegistry::getTableLocator()->get('Articles'));
    }

    public function tearDown(): void
    {
        unset($this->fileUpload);
        unset($this->table);

        parent::tearDown();
    }

    public function testSave(): void
    {
        $data = [
            'tmp_name' => TESTS . 'img' . DS . 'qobo.png',
            'error' => 0,
            'name' => 'qobo.png',
            'type' => 'image/png',
            'size' => 1186,
        ];

        $result = $this->fileUpload->save('image', $data);

        $this->assertInstanceOf(FileStorage::class, $result);
        $this->assertTrue(file_exists(TMP . $result->get('path')));
    }

    public function testSaveWithUppercasedExtension(): void
    {
        $data = [
            'tmp_name' => TESTS . 'img' . DS . 'qobo.PNG',
            'error' => 0,
            'name' => 'qobo.PNG',
            'type' => 'image/png',
            'size' => 1186,
        ];

        $result = $this->fileUpload->save('image', $data);

        $this->assertSame('png', $result->get('extension'));
        $this->assertEventFired('ImageVersion.createVersion');
    }

    public function testSaveAll(): void
    {
        $data = [
            [
                'tmp_name' => TESTS . 'img' . DS . 'qobo.png',
                'error' => 0,
                'name' => 'qobo.png',
                'type' => 'image/png',
                'size' => 1186,
            ],
            [
                'tmp_name' => TESTS . 'img' . DS . 'qobo.png',
                'error' => 0,
                'name' => 'qobo.png',
                'type' => 'image/png',
                'size' => 1186,
            ],
        ];

        $result = $this->fileUpload->saveAll('image', $data);

        $this->assertCount(2, $result);

        foreach ($result as $entity) {
            $this->assertInstanceOf(FileStorage::class, $entity);
            $this->assertTrue(file_exists(TMP . $entity->get('path')));
        }
    }

    public function testSaveAllWithoutFiles(): void
    {
        $this->assertSame([], $this->fileUpload->saveAll('image', []));
    }

    public function testSaveAllWithInvalidData(): void
    {
        $data = ['foo' => 'bar', 'baz' => 'foo'];

        $this->assertSame([], $this->fileUpload->saveAll('image', $data));
    }

    public function testSaveWithMissingParameter(): void
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

    public function testSaveWithError(): void
    {
        $data = [
            'tmp_name' => TESTS . 'img' . DS . 'qobo.png',
            'error' => 1,
            'name' => 'qobo.png',
            'type' => 'image/png',
            'size' => 1186,
        ];

        $this->assertNull($this->fileUpload->save('image', $data));
    }

    public function testGetFiles(): void
    {
        $result = $this->fileUpload->getFiles('image', '00000000-0000-0000-0000-000000000003');

        $this->assertInstanceOf(ResultSetInterface::class, $result);
        $this->assertFalse($result->isEmpty());

        $expected = [
            'huge' => 'tests/img/qobo.png',
            'large' => 'tests/img/qobo.png',
            'medium' => 'tests/img/qobo.png',
            'small' => 'tests/img/qobo.png',
            'tiny' => 'tests/img/qobo.png',
        ];
        $this->assertSame($expected, $result->first()->get('thumbnails'));
    }

    public function testGetFilesWithUppercasedExtension(): void
    {
        $result = $this->fileUpload->getFiles('image', '00000000-0000-0000-0000-000000000004');

        $this->assertInstanceOf(ResultSetInterface::class, $result);
        $this->assertFalse($result->isEmpty());

        $expected = [
            'huge' => 'tests/img/qobo.PNG',
            'large' => 'tests/img/qobo.PNG',
            'medium' => 'tests/img/qobo.PNG',
            'small' => 'tests/img/qobo.PNG',
            'tiny' => 'tests/img/qobo.PNG',
        ];
        $this->assertSame($expected, $result->first()->get('thumbnails'));
    }

    public function testGetFilesUrls(): void
    {
        $this->assertSame([], $this->fileUpload->getFilesUrls('00000000-0000-0000-0000-000000000003', ''));

        $this->assertSame(
            ['tests/img/qobo.png'],
            $this->fileUpload->getFilesUrls('00000000-0000-0000-0000-000000000003', 'image')
        );

        $this->assertSame(
            ['tests/img/qobo.png'],
            $this->fileUpload->getFilesUrls('00000000-0000-0000-0000-000000000003', 'image', 'huge')
        );

        $this->assertSame(
            [''],
            $this->fileUpload->getFilesUrls('00000000-0000-0000-0000-000000000003', 'image', 'invalid-size')
        );
    }

    public function testGetThumbnails(): void
    {
        $expected = [
            'huge' => 'tests/img/qobo.png',
            'large' => 'tests/img/qobo.png',
            'medium' => 'tests/img/qobo.png',
            'small' => 'tests/img/qobo.png',
            'tiny' => 'tests/img/qobo.png',
        ];

        $fileStorage = $this->table->get('00000000-0000-0000-0000-000000000001');

        $this->assertSame($expected, $this->fileUpload->getThumbnails($fileStorage));
    }

    public function testGetThumbnailsWithoutConfiguration(): void
    {
        Configure::write('FileStorage.imageHashes.file_storage', []);
        $fileStorage = $this->table->get('00000000-0000-0000-0000-000000000001');

        $this->assertSame([], $this->fileUpload->getThumbnails($fileStorage));
    }

    public function testGetThumbnail(): void
    {
        $fileStorage = $this->table->get('00000000-0000-0000-0000-000000000001');

        $this->assertSame('tests/img/qobo.png', $this->fileUpload->getThumbnail($fileStorage, 'large'));
    }

    public function testGetThumbnailWithoutConfiguration(): void
    {
        Configure::write('FileStorage.imageHashes.file_storage', []);
        $fileStorage = $this->table->get('00000000-0000-0000-0000-000000000001');

        $this->assertSame('tests/img/qobo.png', $this->fileUpload->getThumbnail($fileStorage, 'medium'));
    }

    public function testGetThumbnailWithInvalidSize(): void
    {
        $fileStorage = $this->table->get('00000000-0000-0000-0000-000000000001');

        $this->assertSame('tests/img/qobo.png', $this->fileUpload->getThumbnail($fileStorage, 'invalid-size'));
    }

    public function testGetThumbnailForNonImage(): void
    {
        $fileStorage = $this->table->get('00000000-0000-0000-0000-000000000003');

        $this->assertSame('/qobo/utils/icons/files/512px/pdf.png', $this->fileUpload->getThumbnail($fileStorage, 'huge'));
        $this->assertSame('/qobo/utils/icons/files/48px/pdf.png', $this->fileUpload->getThumbnail($fileStorage, 'large'));
        $this->assertSame('/qobo/utils/icons/files/32px/pdf.png', $this->fileUpload->getThumbnail($fileStorage, 'medium'));
        $this->assertSame('/qobo/utils/icons/files/16px/pdf.png', $this->fileUpload->getThumbnail($fileStorage, 'small'));
        $this->assertSame('/qobo/utils/icons/files/16px/pdf.png', $this->fileUpload->getThumbnail($fileStorage, 'tiny'));
    }

    public function testGetThumbnailForNonImageWithoutConfiguration(): void
    {
        Configure::write('FileStorage.imageSizes', []);
        $fileStorage = $this->table->get('00000000-0000-0000-0000-000000000003');

        $this->assertSame('Qobo/Utils.icons/files/48px/pdf.png', $this->fileUpload->getThumbnail($fileStorage, 'medium'));
    }

    public function testGetThumbnailForNonImageWithInvalidSize(): void
    {
        Configure::write('FileStorage.imageHashes.file_storage', ['invalid-size' => '']);
        $fileStorage = $this->table->get('00000000-0000-0000-0000-000000000003');

        $this->assertSame('Qobo/Utils.icons/files/48px/pdf.png', $this->fileUpload->getThumbnail($fileStorage, 'invalid-size'));
    }

    public function testGetThumbnailSizeList(): void
    {
        $expected = [
          'huge' => 'Huge (2000 x 2000)',
          'large' => 'Large (1024 x 1024)',
          'medium' => 'Medium (500 x 500)',
          'small' => 'Small (150 x 150)',
          'tiny' => 'Tiny (50 x 50)',
        ];

        $this->assertSame($expected, $this->fileUpload->getThumbnailSizeList());
    }

    public function testFileFields(): void
    {
        $this->assertSame(['image'], FileUpload::fileFields('Articles'));
        $this->assertSame(['field_files'], FileUpload::fileFields('Fields'));
        $this->assertSame([], FileUpload::fileFields('Authors'));
    }

    public function testHasFileFields(): void
    {
        $this->assertTrue(FileUpload::hasFileFields('Articles'));
        $this->assertTrue(FileUpload::hasFileFields('Fields'));
        $this->assertFalse(FileUpload::hasFileFields('Authors'));
    }

    public function testDelete(): void
    {
        $articleId = '00000000-0000-0000-0000-000000000002';

        $fileStorage = $this->fileUpload->save('image', [
            'tmp_name' => TESTS . 'img' . DS . 'qobo.png',
            'error' => 0,
            'name' => 'qobo.png',
            'type' => 'image/png',
            'size' => 1186,
        ]);

        $count = $this->fileUpload->link($articleId, [
            'Articles' => [
                'image_ids' => [$fileStorage->get('id')],
            ],
        ]);

        $this->assertSame(1, $count);

        $this->assertTrue($this->fileUpload->delete($articleId));
    }

    public function testDeleteWithInvalidForeignKey(): void
    {
        $this->assertFalse($this->fileUpload->delete('invalid-foreign-key'));
    }

    public function testLink(): void
    {
        $articleId = '00000000-0000-0000-0000-000000000002';

        $this->assertSame(0, $this->fileUpload->link($articleId, []));

        $data = [
            'Articles' => [
                'image_ids' => ['00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000002'],
            ],
        ];

        $this->assertSame(2, $this->fileUpload->link($articleId, $data));
    }

    public function testCreateThumbnails(): void
    {
        $data = [
            'tmp_name' => TESTS . 'img' . DS . 'qobo.png',
            'error' => 0,
            'name' => 'qobo.png',
            'type' => 'image/png',
            'size' => 1186,
        ];

        $fileStorage = $this->fileUpload->save('image', $data);

        $this->assertTrue($this->fileUpload->createThumbnails($fileStorage));
    }

    public function testRemoveThumbnails(): void
    {
        $data = [
            'tmp_name' => TESTS . 'img' . DS . 'qobo.png',
            'error' => 0,
            'name' => 'qobo.png',
            'type' => 'image/png',
            'size' => 1186,
        ];

        $fileStorage = $this->fileUpload->save('image', $data);

        $this->assertTrue($this->fileUpload->removeThumbnails($fileStorage));
    }
}

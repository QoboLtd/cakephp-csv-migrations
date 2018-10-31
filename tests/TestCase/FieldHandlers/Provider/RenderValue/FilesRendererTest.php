<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use Burzum\FileStorage\Storage\Listener\LocalListener;
use Cake\Core\Configure;
use Cake\Datasource\ModelAwareTrait;
use Cake\Event\EventManager;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\Config\FilesConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\FilesRenderer;

class FilesRendererTest extends TestCase
{
    use ModelAwareTrait;

    public $fixtures = ['plugin.CsvMigrations.file_storage'];

    public function setUp()
    {
        parent::setUp();

        $this->renderer = new FilesRenderer(new FilesConfig('image', $this->loadModel('Articles')));

        // @link https://github.com/burzum/cakephp-file-storage/blob/master/docs/Documentation/Included-Event-Listeners.md
        EventManager::instance()->on(new LocalListener([
            'imageProcessing' => true,
            'pathBuilderOptions' => [
                'pathPrefix' => Configure::read('FileStorage.pathBuilderOptions.pathPrefix')
            ]
        ]));
    }

    public function tearDown()
    {
        unset($this->renderer);

        parent::tearDown();
    }

    public function getValues()
    {
        return [
            [true, ''],
            [false, ''],
            [0, ''],
            [1, ''],
            [-1, ''],
            ['', ''],
            ['foobar', ''],
        ];
    }

    public function testRenderValue()
    {
        $expected = '/tests/img/qobo.png';
        $result = $this->renderer->provide('00000000-0000-0000-0000-000000000003');

        $this->assertContains($expected, $result);
    }

    /**
     * @dataProvider getValues
     */
    public function testRenderValueWithInvalidValue($value, $expected)
    {
        $result = $this->renderer->provide($value);
        $this->assertSame($expected, $result);
    }
}

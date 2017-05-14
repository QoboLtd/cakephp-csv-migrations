<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Renderer;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\Renderer\DblistRenderer;
use CsvMigrations\Model\Table\DblistsTable;

class DblistRendererTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CsvMigrations.Dblists',
        'plugin.CsvMigrations.DblistItems',
    ];

    protected $renderer;

    public function setUp()
    {
        $this->renderer = new DblistRenderer();
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->renderer));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Renderer\RendererInterface', $implementedInterfaces), "RendererInterface is not implemented");
    }

    public function basicValues()
    {
        return [
            ['text', 'text', 'Text'],
            ['<p>HTML</p>', 'HTML', 'HTML'],
            ['<script>alert("hello");</script>', 'alert(&#34;hello&#34;);', 'JavaScript'],
        ];
    }

    /**
     * @dataProvider basicValues
     */
    public function testRenderValueBasic($value, $expected, $description)
    {
        $result = $this->renderer->renderValue($value, ['listName' => null]);
        $this->assertEquals($expected, $result, "Value rendering is broken for: $description");
    }

    public function testRenderValue()
    {
        $result = $this->renderer->renderValue('individual', ['listName' => 'categories']);
        $this->assertEquals('Individual', $result, "Value rendering is broken for dblist parent value");

        $result = $this->renderer->renderValue('antonis', ['listName' => 'categories']);
        $this->assertEquals('Antonis', $result, "Value rendering is broken for dblist child value");
    }
}

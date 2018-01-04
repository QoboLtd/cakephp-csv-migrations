<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Config\ListConfig;
use CsvMigrations\FieldHandlers\Provider\RenderValue\ListRenderer;
use PHPUnit_Framework_TestCase;

class ListRendererTest extends PHPUnit_Framework_TestCase
{
    protected $renderer;

    protected function setUp()
    {
        $config = new ListConfig('list');
        $this->renderer = new ListRenderer($config);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->renderer));
        $this->assertTrue(in_array('CsvMigrations\FieldHandlers\Provider\ProviderInterface', $implementedInterfaces), "ProviderInterface is not implemented");
    }

    public function basicValues()
    {
        return [
            ['text', 'Text', 'Text'],
            ['<p>HTML</p>', 'HTML', 'HTML'],
            ['<script>alert("hello");</script>', 'alert(&#34;hello&#34;);', 'JavaScript'],
        ];
    }

    /**
     * @dataProvider basicValues
     */
    public function testRenderValueBasic($value, $expected, $description)
    {
        $result = $this->renderer->provide($value, ['listItems' => [ $value => $expected ] ]);
        $this->assertEquals($expected, $result, "Value rendering is broken for: $description");
    }

    public function testRenderValueNotFound()
    {
        $result = $this->renderer->provide('text', ['listItems' => ['foo' => 'Foo']]);
        $expected = sprintf(ListRenderer::VALUE_NOT_FOUND_HTML, 'text');
        $this->assertEquals($expected, $result, "Value rendering is broken for missing text value");

        $result = $this->renderer->provide('<p>HTML</p>', ['listItems' => ['foo' => 'Foo']]);
        $expected = sprintf(ListRenderer::VALUE_NOT_FOUND_HTML, 'HTML');
        $this->assertEquals($expected, $result, "Value rendering is broken for missing HTML value");
    }

    public function testRenderValue()
    {
        $listItems = [
            'parent' => 'Parent',
            'parent.child' => ':Child',
            'parent.child.grandchild' => '-GrandChild',
        ];
        $result = $this->renderer->provide('parent', ['listItems' => $listItems]);
        $expected = 'Parent';
        $this->assertEquals($expected, $result, "Value rendering is broken for parent list value");

        $result = $this->renderer->provide('parent.child', ['listItems' => $listItems]);
        $expected = 'Parent:Child';
        $this->assertEquals($expected, $result, "Value rendering is broken for child list value");

        $result = $this->renderer->provide('parent.child.grandchild', ['listItems' => $listItems]);
        $expected = 'Parent:Child-GrandChild';
        $this->assertEquals($expected, $result, "Value rendering is broken for grandchild list value");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderExceptionListItems()
    {
        $result = $this->renderer->provide('test', ['listItems' => 'not_an_array']);
    }
}

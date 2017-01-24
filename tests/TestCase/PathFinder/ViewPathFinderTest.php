<?php
namespace CsvMigrations\Test\TestCase\PathFinder;

use Cake\Core\Configure;
use CsvMigrations\PathFinder\ViewPathFinder;
use PHPUnit_Framework_TestCase;

class ViewPathFinderTest extends PHPUnit_Framework_TestCase
{
    protected $pf;

    protected function setUp()
    {
        $this->pf = new ViewPathFinder();
        $dir = dirname(dirname(__DIR__)) . DS . 'data' . DS . 'CsvMigrations' . DS . 'views' . DS;
        Configure::write('CsvMigrations.views.path', $dir);
    }

    public function testInterface()
    {
        $implementedInterfaces = array_keys(class_implements($this->pf));
        $this->assertTrue(in_array('CsvMigrations\PathFinder\PathFinderInterface', $implementedInterfaces), "PathFinderInterface is not implemented");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFind()
    {
        $path = $this->pf->find('Foo');
    }

    public function testFindAdd()
    {
        $path = $this->pf->find('Foo', 'add');
        $this->assertFalse(empty($path), "Path is empty [$path]");
        $this->assertTrue(is_string($path), "Path is not a string [$path]");
        $this->assertTrue(file_exists($path), "Path does not exist [$path]");
        $this->assertTrue(is_readable($path), "Path is not readable [$path]");
        $this->assertTrue(is_file($path), "Path is not a file [$path]");
    }

    public function testFindAddFull()
    {
        $path = $this->pf->find('Foo', 'add.csv');
        $this->assertFalse(empty($path), "Path is empty [$path]");
        $this->assertTrue(is_string($path), "Path is not a string [$path]");
        $this->assertTrue(file_exists($path), "Path does not exist [$path]");
        $this->assertTrue(is_readable($path), "Path is not readable [$path]");
        $this->assertTrue(is_file($path), "Path is not a file [$path]");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindExceptionModuleEmpty()
    {
        $path = $this->pf->find(null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindExceptionModuleNotString()
    {
        $path = $this->pf->find(['foo' => 'bar']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindExceptionPathNotString()
    {
        $path = $this->pf->find('Foo', ['foo' => 'bar']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindExceptionPathNotExist()
    {
        $path = $this->pf->find('Foo', 'some_custom.csv');
    }
}

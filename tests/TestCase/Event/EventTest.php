<?php
namespace CsvMigrations\Test\TestCase\Event;

use Cake\Event\EventListenerInterface;
use Cake\TestSuite\IntegrationTestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use RegexIterator;

class EventTest extends IntegrationTestCase
{
    /**
     * @var string $dir Path to look for event class files
     */
    protected $dir = ROOT . DS . 'src' . DS . 'Event';

    /**
     * @var string $namespace Base namespace for event classes
     */
    protected $namespace = '\\CsvMigrations\\Event';

    /**
     * @var string $interface Required interface for all event classes to implement
     */
    protected $interface = 'Cake\\Event\\EventListenerInterface';

    /**
     * classProvider
     *
     * Find all event classes in a given path and return
     * them as full class names with namespace.
     *
     * @return array
     */
    public function classProvider()
    {
        $result = [];

        $eventsDir = $this->dir;
        $eventsNameSpace = $this->namespace;

        $directory = new RecursiveDirectoryIterator($eventsDir);
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+\.php$/', RecursiveRegexIterator::GET_MATCH);
        foreach ($regex as $file) {
            $file = $file[0];
            if ('EventName' === pathinfo($file, PATHINFO_FILENAME)) {
                continue;
            }
            // Remove file extension
            $file = pathinfo($file, PATHINFO_DIRNAME) . DS . pathinfo($file, PATHINFO_FILENAME);
            // Remove path prefix
            $file = substr($file, strlen($eventsDir));
            // Switch to namespace
            $file = str_replace(DS, '\\', $file);
            // Prefix namespace
            $file = $eventsNameSpace . $file;
            $result[] = [$file];
        }

        return $result;
    }

    /**
     * testEvents
     *
     * Basic tests for all event classes:
     *
     * * required interface is implemented
     * * implementedEvents() returns a non-empty array response
     * * each event handler is actually callable
     *
     * @dataProvider classProvider
     */
    public function testEvents($class)
    {
        $reflection = new ReflectionClass($class);
        // Avoid checking abstract classes, interfaces, and the like
        if (!$reflection->isInstantiable()) {
            $this->markTestSkipped("Class [$class] is not instantiable");
        }

        // All event classes must implement EventListenerInterface
        $requiredInterface = $this->interface;
        $this->assertTrue($reflection->implementsInterface($requiredInterface), "Class [$class] does not implement [$requiredInterface]");

        // Instantiate the event class and get implemented events list
        $event = new $class();
        $implemented = $event->implementedEvents();
        $this->assertTrue(is_array($implemented), "implementedEvents() of [$class] returned a non-array");
        $this->assertFalse(empty($implemented), "implementedEvents() of [$class] returned an empty array");

        // Test that we each event's handler is actually callable
        // See: https://api.cakephp.org/3.4/class-Cake.Event.EventListenerInterface.html#_implementedEvents
        foreach ($implemented as $name => $handler) {
            if (is_array($handler)) {
                $this->assertFalse(empty($handler['callable']), "Handler for event [$name] in [$class] is missing 'callable' key");
                $this->assertTrue(is_string($handler['callable']), "Handler for event [$name] in [$class] has a non-string 'callable' key");
                $handler = $handler['callable'];
            }

            $this->assertTrue(method_exists($event, $handler), "Method [$handler] does not exist in [$class] for event [$name]");
            $this->assertTrue(is_callable([$event, $handler]), "Method [$handler] is not callable in [$class] for event [$name]");
        }
    }
}

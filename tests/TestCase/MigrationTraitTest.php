<?php
namespace CsvMigrations\Test\TestCase;

use Cake\Core\Configure;
use Cake\Event\Event;
use CsvMigrations\MigrationTrait;
use PHPUnit_Framework_TestCase;

class MigrationTraitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->mock = $this->getMockForTrait(MigrationTrait::class);

        $dir = dirname(__DIR__) . DS . 'data' . DS . 'CsvMigrations' . DS . 'migrations' . DS;
        Configure::write('CsvMigrations.migrations.path', $dir);
    }


    /**
     * @expectedException RuntimeException
     */
    public function testGetFieldsDefinitionsThrowsException()
    {
        $this->mock->getFieldsDefinitions();
    }

}

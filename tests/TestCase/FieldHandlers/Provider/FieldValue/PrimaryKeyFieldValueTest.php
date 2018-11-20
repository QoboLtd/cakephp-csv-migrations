<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\FieldValue;

use Cake\Http\ServerRequest;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\Config\FilesConfig;
use CsvMigrations\FieldHandlers\Provider\FieldValue\PrimaryKeyFieldValue;
use CsvMigrations\FieldHandlers\Provider\ProviderInterface;

class PrimaryKeyFieldValueTest extends TestCase
{
    public $fixtures = [
        'plugin.csv_migrations.foo'
    ];

    public function testConstruct() : void
    {
        $provider = new PrimaryKeyFieldValue(new FilesConfig('foobar'));
        $this->assertInstanceOf(ProviderInterface::class, $provider);
    }

    public function testProvideWithExistingTable() : void
    {
        // field name is not used within PrimaryKeyFieldValue provider
        $fieldName = 'foobar';

        $provider = new PrimaryKeyFieldValue(new FilesConfig($fieldName, 'Foo'));

        $entity = new Entity();
        $entity->set('id', '123');

        $this->assertEquals('123', $provider->provide(null, ['entity' => $entity]));
    }

    /**
     * @param mixed $value
     * @dataProvider valuesDataProvider
     */
    public function testProvideWithDummyTable($value) : void
    {
        // field name is not used within PrimaryKeyFieldValue provider
        $fieldName = 'foobar';

        $provider = new PrimaryKeyFieldValue(new FilesConfig($fieldName, 'nonExistingTable'));

        $this->assertEquals(null, $provider->provide($value, ['entity' => new Entity()]));
    }

    /**
     * @param mixed $value
     * @dataProvider valuesDataProvider
     */
    public function testProvideWithDummyTableWithoutEntity($value) : void
    {
        // field name is not used within PrimaryKeyFieldValue provider
        $fieldName = 'foobar';

        $provider = new PrimaryKeyFieldValue(new FilesConfig($fieldName, 'nonExistingTable'));

        $this->assertEquals(null, $provider->provide($value));
    }

    /**
     * @return mixed[]
     */
    public function valuesDataProvider() : array
    {
        return [
            [null],
            ['here goes some string'],
            [new Entity()],
            [new ServerRequest()]
        ];
    }
}

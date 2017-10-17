<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * Foo Entity.
 *
 */
class Foo extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];
}

class FieldHandlerFactoryTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CsvMigrations.foo'
    ];

    /**
     * Test subject
     *
     * @var CsvMigrations\FieldHandlers\FieldHandlerFactory
     */
    public $fhf;

    /**
     * Table instance
     *
     * @var Cake\ORM\Table
     */
    public $table;

    /**
     * Csv Data
     *
     * @var array
     */
    public $csvData;

    /**
     * Table name
     *
     * @var string
     */
    public $tableName = 'Foo';

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $dir = dirname(__DIR__) . DS . '..' . DS . 'data' . DS . 'Modules' . DS;
        Configure::write('CsvMigrations.modules.path', $dir);

        $mc = new ModuleConfig(ConfigType::MIGRATION(), $this->tableName);
        $this->csvData = (array)json_decode(json_encode($mc->parse()), true);

        $config = TableRegistry::exists($this->tableName)
            ? []
            : ['className' => 'CsvMigrations\Test\TestCase\Model\Table\FooTable'];
        $this->table = TableRegistry::get($this->tableName, $config);

        $this->fhf = new FieldHandlerFactory();
    }

    public function testRenderInput()
    {
        $result = $this->fhf->renderInput($this->table, 'id');
        $this->assertRegexp('/input/i', $result, "Rendering input for 'id' field has no 'input'");
    }

    public function testRenderName()
    {
        $result = $this->fhf->renderName($this->table, 'testField');
        $this->assertEquals('Test Field', $result);

        $result = $this->fhf->renderName($this->table, 'related_files._ids');
        $this->assertEquals('Related Files', $result);

        $result = $this->fhf->renderName($this->table, 'some.related.field_name');
        $this->assertEquals('Field Name', $result);

        $result = $this->fhf->renderName($this->table, 'related_field_id');
        $this->assertEquals('Related Field', $result);
    }

    public function testRenderValue()
    {
        $result = $this->fhf->renderValue($this->table, 'id', 'blah');
        $this->assertRegexp('/blah/i', $result, "Rendering value 'blah' for 'id' field has no 'blah'");
    }

    public function testFieldToDb()
    {
        $csvField = new CsvField(['name' => 'blah', 'type' => 'string']);
        $result = $this->fhf->fieldToDb($csvField, $this->table, 'id');
        $this->assertTrue(is_array($result), "fieldToDb() method does not return an array");
        $this->assertFalse(empty($result), "fieldToDb() method returns an empty array");
    }

    public function testHasFieldHandler()
    {
        $result = $this->fhf->hasFieldHandler('string');
        $this->assertTrue($result, "Failed to find field handler for type 'string'");

        $result = $this->fhf->hasFieldHandler('non-existing-field-type');
        $this->assertFalse($result, "Found field handler for type 'non-existing-field-type'");
    }
}

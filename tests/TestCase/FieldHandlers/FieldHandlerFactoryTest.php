<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\FieldHandlers\FieldHandlerInterface;
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
    public $fixtures = ['plugin.CsvMigrations.Foo'];

    private $table;
    private $fhf;
    private $csvData;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Configure::write('CsvMigrations.modules.path', TESTS . 'config' . DS . 'Modules' . DS);

        $mc = new ModuleConfig(ConfigType::MIGRATION(), 'Foo');
        $this->csvData = $mc->parseToArray();

        $config = TableRegistry::getTableLocator()->exists('Foo') ? [] : ['className' => 'CsvMigrations\Test\App\Model\Table\FooTable'];
        $this->table = TableRegistry::getTableLocator()->get('Foo', $config);

        $this->fhf = new FieldHandlerFactory();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->fhf);
        unset($this->table);
        unset($this->csvData);

        parent::tearDown();
    }

    public function testGetByTableField(): void
    {
        $result = FieldHandlerFactory::getByTableField($this->table, 'id');
        $this->assertFalse(empty($result), "FieldHandlerFactory returned an empty result");
        $this->assertTrue($result instanceof FieldHandlerInterface, "FieldHandlerFactory returned incorrect instance");
    }

    public function testRenderInput(): void
    {
        $result = $this->fhf->renderInput($this->table, 'id');
        $this->assertRegexp('/input/i', $result, "Rendering input for 'id' field has no 'input'");
    }

    public function testRenderName(): void
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

    public function testGetSearchOptions(): void
    {
        $fieldDefinitions = [
            CsvField::FIELD_NAME => 'id',
            CsvField::FIELD_TYPE => 'string',
            CsvField::FIELD_NON_SEARCHABLE => true,
        ];
        $result = $this->fhf->getSearchOptions($this->table, 'id', ['fieldDefinitions' => $fieldDefinitions]);
        $this->assertTrue(is_array($result), "getSearchOptions() returned a non-array result");
        $this->assertTrue(empty($result), "getSearchOptions() returned a non-empty result");
    }

    public function testRenderValue(): void
    {
        $result = $this->fhf->renderValue($this->table, 'id', 'blah');
        $this->assertRegexp('/blah/i', $result, "Rendering value 'blah' for 'id' field has no 'blah'");
    }

    public function testFieldToDb(): void
    {
        $csvField = new CsvField(['name' => 'blah', 'type' => 'string']);
        $result = $this->fhf->fieldToDb($csvField, $this->table, 'id');
        $this->assertTrue(is_array($result), "fieldToDb() method does not return an array");
        $this->assertFalse(empty($result), "fieldToDb() method returns an empty array");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFieldToDbException(): void
    {
        $csvField = new CsvField(['name' => 'blah', 'type' => 'foobar']);
        $result = $this->fhf->fieldToDb($csvField, $this->table, 'id');
    }
}

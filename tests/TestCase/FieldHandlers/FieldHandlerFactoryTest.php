<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\MigrationTrait;

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
     * @var Cake\ORM\Table
     */
    public $FooTable;

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

        $dir = dirname(__DIR__) . DS . '..' . DS . 'data' . DS . 'CsvMigrations' . DS;
        Configure::write('CsvMigrations.migrations.path', $dir . 'migrations' . DS);
        Configure::write('CsvMigrations.lists.path', $dir . 'lists' . DS);
        Configure::write('CsvMigrations.migrations.filename', 'migration.dist');

        $mockTrait = $this->getMockForTrait(MigrationTrait::class);
        $this->csvData = $mockTrait->getFieldsDefinitions($this->tableName);
        $config = TableRegistry::exists($this->tableName)
            ? []
            : ['className' => 'CsvMigrations\Test\TestCase\Model\Table\FooTable'];
        $this->FooTable = TableRegistry::get($this->tableName, $config);

        $this->fhf = new FieldHandlerFactory();
    }

    public function testRenderInput()
    {
        $foos = $this->FooTable->find();
        $provider = $this->renderInputsProvider();
        foreach ($foos as $k => $foo) {
            foreach (array_keys($this->csvData) as $field) {
                $expected = $provider[$k][$field];
                $result = $this->fhf->renderInput($this->FooTable, $field, $foo->{$field}, ['entity' => $foo]);
                $this->assertSame($expected, $result);
            }
        }
    }

    public function testRenderValue()
    {
        $foos = $this->FooTable->find();
        $provider = $this->renderValuesProvider();
        foreach ($foos as $k => $foo) {
            foreach (array_keys($this->csvData) as $field) {
                $expected = $provider[$k][$field];
                $result = $this->fhf->renderValue($this->FooTable, $field, $foo->{$field}, ['entity' => $foo]);
                $this->assertSame($expected, $result);
            }
        }
    }

    /**
     * Method responsible for converting csv field instance to database field instance.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array list of DbField instances
     */
    public function fieldToDb(CsvField $csvField)
    {
        $dbFields[] = new DbField(
            $csvField->getName(),
            $csvField->getType(),
            $csvField->getLimit(),
            $csvField->getRequired(),
            $csvField->getNonSearchable(),
            $csvField->getUnique()
        );

        return $dbFields;
    }

    public function renderValuesProvider()
    {
        return [
            [
                'id' => 'd8c3ba90-c418-4e58-8cb6-b65c9095a2dc',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                'name' => 'Foobar',
                'status' => 'Active',
                'type' => 'Gold',
                'gender' => 'Male',
                'city' => 'Limassol',
                'country' => 'Cyprus',
                'cost' => '1000 EUR',
                'birthdate' => '1985-04-22',
                'created' => '2016-07-01 10:39',
                'modified' => '2016-07-01 10:41'
            ]
        ];
    }

    public function renderInputsProvider()
    {
        return [
            [
                'id' => '<div class="form-group text"><label class="control-label" for="foo-id">Id</label><input type="text" name="Foo[id]" id="foo-id" class="form-control" value="d8c3ba90-c418-4e58-8cb6-b65c9095a2dc"/></div>',
                'description' => '<div class="form-group textarea"><label class="control-label" for="foo-description">Description</label><textarea name="Foo[description]" id="foo-description" class="form-control" rows="5">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</textarea></div>',
                'name' => '<div class="form-group text required"><label class="control-label" for="foo-name">Name</label><input type="text" name="Foo[name]" required="required" id="foo-name" class="form-control" value="Foobar"/></div>',
                'status' => '<div class="form-group"><label for="status">Status</label><select name="Foo[status]" class="form-control" required="required"><option value="active" selected="selected">Active</option><option value="inactive">Inactive</option></select></div>',
                'type' => '<div class="form-group"><label for="type">Type</label><select name="Foo[type]" class="form-control" required="required"><option value="bronze">Bronze</option><option value="bronze.new">New</option><option value="bronze.used">Used</option><option value="silver">Silver</option><option value="silver.new">New</option><option value="silver.used">Used</option><option value="gold" selected="selected">Gold</option><option value="gold.new">New</option><option value="gold.used">Used</option></select></div>',
                'gender' => '<div class="form-group"><label for="gender">Gender</label><select name="Foo[gender]" class="form-control"><option value="m" selected="selected">Male</option><option value="f">Female</option></select></div>',
                'city' => '<div class="form-group"><label for="city">City</label><select name="Foo[city]" class="form-control"><option value="limassol" selected="selected">Limassol</option><option value="new_york">New York</option><option value="london">London</option></select></div>',
                'country' => '<div class="form-group"><label for="country">Country</label><select name="Foo[country]" class="form-control"><option value="cy" selected="selected">Cyprus</option><option value="usa">USA</option><option value="uk">United Kingdom</option></select></div>',
                'cost' => '<label for="cost-amount">Cost Amount</label><div class="row"><div class="col-xs-6"><div class="form-group text"><input type="text" name="Foo[cost_amount]" id="foo-cost-amount" class="form-control" value="1000"/></div></div><div class="col-xs-6 col-sm-4 col-sm-offset-2"><select name="Foo[cost_currency]" class="form-control"><option value="eur" selected="selected">EUR</option><option value="usd">USD</option><option value="gpb">GPB</option></select></div></div>',
                'birthdate' => '<div class="form-group">
            <label for="foo-birthdate">Birthdate</label>        <div class=\'input-group date datepicker\'>
        <div class="form-group text"><input type="text" name="Foo[birthdate]" id="foo-birthdate" class="form-control" value="1985-04-22"/></div>                    <span class="input-group-addon">
                <span class="glyphicon glyphicon-calendar"></span>
            </span>
            </div>
</div>
',
                'created' => '<div class="form-group">
            <label for="foo-created">Created</label>        <div class=\'input-group date datetimepicker\'>
        <div class="form-group text"><input type="text" name="Foo[created]" id="foo-created" class="form-control" value="7/1/16, 10:39 AM"/></div>                    <span class="input-group-addon">
                <span class="glyphicon glyphicon-calendar"></span>
            </span>
            </div>
</div>
',
                'modified' => '<div class="form-group">
            <label for="foo-modified">Modified</label>        <div class=\'input-group date datetimepicker\'>
        <div class="form-group text"><input type="text" name="Foo[modified]" id="foo-modified" class="form-control" value="7/1/16, 10:41 AM"/></div>                    <span class="input-group-addon">
                <span class="glyphicon glyphicon-calendar"></span>
            </span>
            </div>
</div>
'
            ]
        ];
    }
}

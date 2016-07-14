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
                'modified' => '2016-07-01 10:41',
                'garden_area' => '50 m&sup2;',
                'is_primary' => 'Yes'
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
                'birthdate' => '<div class="form-group date"><label class="control-label">Birthdate</label><ul class="list-inline"><li class="year"><select name="Foo[birthdate][year]" class="form-control"><option value="2021">2021</option><option value="2020">2020</option><option value="2019">2019</option><option value="2018">2018</option><option value="2017">2017</option><option value="2016">2016</option><option value="2015">2015</option><option value="2014">2014</option><option value="2013">2013</option><option value="2012">2012</option><option value="2011">2011</option><option value="2010">2010</option><option value="2009">2009</option><option value="2008">2008</option><option value="2007">2007</option><option value="2006">2006</option><option value="2005">2005</option><option value="2004">2004</option><option value="2003">2003</option><option value="2002">2002</option><option value="2001">2001</option><option value="2000">2000</option><option value="1999">1999</option><option value="1998">1998</option><option value="1997">1997</option><option value="1996">1996</option><option value="1995">1995</option><option value="1994">1994</option><option value="1993">1993</option><option value="1992">1992</option><option value="1991">1991</option><option value="1990">1990</option><option value="1989">1989</option><option value="1988">1988</option><option value="1987">1987</option><option value="1986">1986</option><option value="1985" selected="selected">1985</option></select></li><li class="month"><select name="Foo[birthdate][month]" class="form-control"><option value="01">January</option><option value="02">February</option><option value="03">March</option><option value="04" selected="selected">April</option><option value="05">May</option><option value="06">June</option><option value="07">July</option><option value="08">August</option><option value="09">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option></select></li><li class="day"><select name="Foo[birthdate][day]" class="form-control"><option value="01">1</option><option value="02">2</option><option value="03">3</option><option value="04">4</option><option value="05">5</option><option value="06">6</option><option value="07">7</option><option value="08">8</option><option value="09">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22" selected="selected">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option></select></li><li class="hour"></li><li class="minute"></li><li class="second"></li><li class="meridian"></li></ul></div>',
                'created' => '<div class="form-group datetime"><label class="control-label">Created</label><ul class="list-inline"><li class="year"><select name="Foo[created][year]" class="form-control"><option value="2021">2021</option><option value="2020">2020</option><option value="2019">2019</option><option value="2018">2018</option><option value="2017">2017</option><option value="2016" selected="selected">2016</option><option value="2015">2015</option><option value="2014">2014</option><option value="2013">2013</option><option value="2012">2012</option><option value="2011">2011</option></select></li><li class="month"><select name="Foo[created][month]" class="form-control"><option value="01">January</option><option value="02">February</option><option value="03">March</option><option value="04">April</option><option value="05">May</option><option value="06">June</option><option value="07" selected="selected">July</option><option value="08">August</option><option value="09">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option></select></li><li class="day"><select name="Foo[created][day]" class="form-control"><option value="01" selected="selected">1</option><option value="02">2</option><option value="03">3</option><option value="04">4</option><option value="05">5</option><option value="06">6</option><option value="07">7</option><option value="08">8</option><option value="09">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option></select></li><li class="hour"><select name="Foo[created][hour]" class="form-control"><option value="00">0</option><option value="01">1</option><option value="02">2</option><option value="03">3</option><option value="04">4</option><option value="05">5</option><option value="06">6</option><option value="07">7</option><option value="08">8</option><option value="09">9</option><option value="10" selected="selected">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option></select></li><li class="minute"><select name="Foo[created][minute]" class="form-control"><option value="00">00</option><option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option><option value="32">32</option><option value="33">33</option><option value="34">34</option><option value="35">35</option><option value="36">36</option><option value="37">37</option><option value="38">38</option><option value="39" selected="selected">39</option><option value="40">40</option><option value="41">41</option><option value="42">42</option><option value="43">43</option><option value="44">44</option><option value="45">45</option><option value="46">46</option><option value="47">47</option><option value="48">48</option><option value="49">49</option><option value="50">50</option><option value="51">51</option><option value="52">52</option><option value="53">53</option><option value="54">54</option><option value="55">55</option><option value="56">56</option><option value="57">57</option><option value="58">58</option><option value="59">59</option></select></li><li class="second"></li><li class="meridian"></li></ul></div>',
                'modified' => '<div class="form-group datetime"><label class="control-label">Modified</label><ul class="list-inline"><li class="year"><select name="Foo[modified][year]" class="form-control"><option value="2021">2021</option><option value="2020">2020</option><option value="2019">2019</option><option value="2018">2018</option><option value="2017">2017</option><option value="2016" selected="selected">2016</option><option value="2015">2015</option><option value="2014">2014</option><option value="2013">2013</option><option value="2012">2012</option><option value="2011">2011</option></select></li><li class="month"><select name="Foo[modified][month]" class="form-control"><option value="01">January</option><option value="02">February</option><option value="03">March</option><option value="04">April</option><option value="05">May</option><option value="06">June</option><option value="07" selected="selected">July</option><option value="08">August</option><option value="09">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option></select></li><li class="day"><select name="Foo[modified][day]" class="form-control"><option value="01" selected="selected">1</option><option value="02">2</option><option value="03">3</option><option value="04">4</option><option value="05">5</option><option value="06">6</option><option value="07">7</option><option value="08">8</option><option value="09">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option></select></li><li class="hour"><select name="Foo[modified][hour]" class="form-control"><option value="00">0</option><option value="01">1</option><option value="02">2</option><option value="03">3</option><option value="04">4</option><option value="05">5</option><option value="06">6</option><option value="07">7</option><option value="08">8</option><option value="09">9</option><option value="10" selected="selected">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option></select></li><li class="minute"><select name="Foo[modified][minute]" class="form-control"><option value="00">00</option><option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option><option value="32">32</option><option value="33">33</option><option value="34">34</option><option value="35">35</option><option value="36">36</option><option value="37">37</option><option value="38">38</option><option value="39">39</option><option value="40">40</option><option value="41" selected="selected">41</option><option value="42">42</option><option value="43">43</option><option value="44">44</option><option value="45">45</option><option value="46">46</option><option value="47">47</option><option value="48">48</option><option value="49">49</option><option value="50">50</option><option value="51">51</option><option value="52">52</option><option value="53">53</option><option value="54">54</option><option value="55">55</option><option value="56">56</option><option value="57">57</option><option value="58">58</option><option value="59">59</option></select></li><li class="second"></li><li class="meridian"></li></ul></div>',
                'garden_area' => '<label for="garden-area-amount">Garden Area Amount</label><div class="row"><div class="col-xs-6"><div class="form-group number"><input type="number" name="Foo[garden_area_amount]" id="foo-garden-area-amount" class="form-control" value="50"/></div></div><div class="col-xs-6 col-sm-4 col-sm-offset-2"><select name="Foo[garden_area_unit]" class="form-control"><option value="m" selected="selected">m&sup2;</option><option value="ft">ft&sup2;</option></select></div></div>',
                'is_primary' => '<div class="form-group checkbox"><input type="hidden" name="Foo[is_primary]" value="0"/><label for="foo-is-primary"><input type="checkbox" name="Foo[is_primary]" value="1" checked="checked" id="foo-is-primary">Is Primary</label></div>'
            ]
        ];
    }
}

<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\Core\Configure;
use Cake\Orm\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Panel;
use CsvMigrations\Table;
use \RuntimeException;

class PanelTest extends TestCase
{
    /**
     * PanelTable instance
     *
     * @var CsvMigrations\Test\TestCase\Model\Table\Panel
     */
    public $table;

    /**
     * Table's config
     * @var array
     */
    public $config;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $dir = dirname(__DIR__) . DS . 'data' . DS . 'CsvMigrations' . DS;
        Configure::write('CsvMigrations.migrations.path', $dir . 'migrations' . DS);
        Configure::write('CsvMigrations.lists.path', $dir . 'lists' . DS);
        Configure::write('CsvMigrations.migrations.filename', 'migration.dist');
        $config = TableRegistry::exists('Panel') ? [] : ['className' => 'CsvMigrations\Test\TestCase\Model\Table\PanelTable'];
        $this->table = TableRegistry::get('Panel', $config);
        $this->config = $this->table->getConfig();
        $panels = Panel::getPanelNames($this->config);
        $this->first = array_pop($panels);
    }

    public function testgetName()
    {
        $panel = new Panel($this->first, $this->config);
        $expected = $this->first;

        $this->assertEquals($expected, $panel->getName());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testSetNameException()
    {
        $panel = new Panel($this->first, $this->config);
        $panel->setName();
    }

    public function testGetExpression()
    {
        $panel = new Panel($this->first, $this->config);
        $expression = $panel->getExpression();

        $this->assertFalse(empty($expression), 'Expression should not be empty');
        $this->assertTrue(is_string($expression), 'Expression should be string`');
        $this->assertContains(Panel::EXP_TOKEN, $expression, sprintf('Expression\'s placeholders have %s', Panel::EXP_TOKEN));
        $expressionWithoutTokens = $panel->getExpression(true);
        $this->assertFalse(strpos($expressionWithoutTokens, Panel::EXP_TOKEN), sprintf('Clean expression should not have any %s', Panel::EXP_TOKEN));
    }

    public function testGetFields()
    {
        $panel = new Panel($this->first, $this->config);
        $fields = $panel->getFields();
        $this->assertTrue(!empty($fields), 'Fields should not be empty');
        $this->assertTrue(is_array($fields), 'Fields should be array');

        $panelName = 'Foobar';
        $config['panels']['Foobar'] = "(%%type%% == 'foobar' && %%name%% == 'antonis')";
        $panelB = new Panel($panelName, $config);

        $this->assertTrue(in_array('type', $panelB->getFields()), 'Given field name is not in the array');
        $this->assertTrue(in_array('name', $panelB->getFields()), 'Given field name is not in the array');
    }


    public function testGetFieldValues()
    {
        $panelName = 'Foobar';
        $config['panels']['Foobar'] = "(%%type%% == 'foobar' && %%name%% == 'somename')";
        $panel = new Panel($panelName, $config);
        $data = ['type' => 'company', 'name' => 'amazon'];
        $actual = $panel->getFieldValues($data);
        $expected = ['type' => 'company', 'name' => 'amazon'];
        $this->assertEquals($actual, $expected, 'Data and field values should be equal.');

        $data = [];
        $actual = $panel->getFieldValues($data);
        $expected = [];
        $this->assertEquals($actual, $expected, 'When data is empty the the field values should be empty.');
    }

    /**
     * @dataProvider evalExpressionScenariosProvider
     */
    public function testEvalExpression($scenario, $expression, $data, $expected)
    {
        $panelName = 'Foobar';
        $config['panels']['Foobar'] = $expression;
        $panel = new Panel($panelName, $config);
        $this->assertEquals($expected, $panel->evalExpression($data), sprintf('%s - Expression evaluation failed', $scenario));
        unset($panel);
    }

    public function evalExpressionScenariosProvider()
    {
        return [
            [
                'Scenario 1 - string comparison',
                "%%status%% == 'first attempt'",
                ['status' => 'first attempt'],
                true
            ],
            [
                'Scenario 2 - string with special character',
                "%%status%% == 'attempt #1'",
                ['status' => 'attempt #1'],
                true
            ],
            [
                'Scenario 3 - logical operator AND',
                "%%status%% == 'active' && %%active%% == false",
                ['status' => 'active', 'active' => false],
                true
            ],
            [
                'Scenario 4 - logical operator NOT',
                "!(%%status%% == 'active')",
                ['status' => 'deactive'],
                true
            ],
            [
                'Scenario 5 - logical operator OR',
                "%%status%% == 'active' || %%active%% == false",
                ['status' => 'active', 'active' => true],
                true
            ],
        ];
    }

    public function testGetPanels()
    {
        $config['panels']['Company'] = "(%%type%% == 'individual')";
        $config['panels']['Personal'] = "(%%type%% == 'company')";
        $actual = Panel::getPanelNames($config);
        $expected = ['Company', 'Personal'];

        $this->assertEquals($actual, $expected, 'Does not return the actual panel names from config.');

        $config = [];
        $actual = Panel::getPanelNames($config);
        $expected = false;

        $this->assertEquals($actual, $expected, 'On an empty config, the function should return false.');
    }
}

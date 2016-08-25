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

        //Should not be empty.
        $this->assertTrue(!empty($expression));
        //Should be string
        $this->assertTrue(is_string($expression));
        //Check for tokens
        $this->assertContains(Panel::EXP_TOKEN, $expression);
        $expressionWithoutTokens = $panel->getExpression(true);
        $this->assertFalse(strpos($expressionWithoutTokens, Panel::EXP_TOKEN));
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

    public function testEvalExpression()
    {
        $scenario = 'Scenario 1 - string comparison';
        $panelName = 'Foobar';
        $config['panels']['Foobar'] = "%%status%% == 'first attempt'";
        $panel = new Panel($panelName, $config);
        $data = ['status' => 'first attempt'];
        $this->assertTrue($panel->evalExpression($data), sprintf('%s - Expression evaluation failed', $scenario));
        unset($panel, $scenario);

        $scenario = 'Scenario 2 - string with special character';
        $panelName = 'Foobar';
        $config['panels']['Foobar'] = "%%status%% == 'attempt #1'";
        $panel = new Panel($panelName, $config);
        $data = ['status' => 'attempt #1'];
        $this->assertTrue($panel->evalExpression($data), sprintf('%s - Expression evaluation failed', $scenario));

        $scenario = 'Scenario 3 - logical operator AND';
        $panelName = 'Foobar';
        $config['panels']['Foobar'] = "%%status%% == 'active' && %%active%% == false";
        $panel = new Panel($panelName, $config);
        $data = ['status' => 'active', 'active' => false];
        $this->assertTrue($panel->evalExpression($data), sprintf('%s - Expression evaluation failed', $scenario));
        unset($panel, $scenario);

        $scenario = 'Scenario 4 - logical operator NOT';
        $panelName = 'Foobar';
        $config['panels']['Foobar'] = "!(%%status%% == 'active')";
        $panel = new Panel($panelName, $config);
        $data = ['status' => 'deactive'];
        $this->assertTrue($panel->evalExpression($data), sprintf('%s - Expression evaluation failed', $scenario));
        unset($panel, $scenario);

        $scenario = 'Scenario 5 - logical operator OR';
        $panelName = 'Foobar';
        $config['panels']['Foobar'] = "%%status%% == 'active' || %%active%% == false";
        $panel = new Panel($panelName, $config);
        $data = ['status' => 'active', 'active' => true];
        $this->assertTrue($panel->evalExpression($data), sprintf('%s - Expression evaluation failed', $scenario));
        unset($panel, $scenario);
    }
}

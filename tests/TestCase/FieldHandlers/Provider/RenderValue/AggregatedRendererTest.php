<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderValue;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\Config\AggregatedConfig;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\Provider\ProviderInterface;
use CsvMigrations\FieldHandlers\Provider\RenderValue\AggregatedRenderer;

class AggregatedRendererTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.foo',
        'plugin.CsvMigrations.leads'
    ];

    public function setUp()
    {
        $this->renderer = new AggregatedRenderer(new AggregatedConfig('aggregated', 'Leads'));
        $this->entity = TableRegistry::get('Leads')->get('00000000-0000-0000-0000-000000000001');
    }

    public function tearDown()
    {
        unset($this->entity);
        unset($this->renderer);
    }

    public function testInterface()
    {
        $this->assertInstanceOf(ProviderInterface::class, $this->renderer);
    }

    public function testProvide()
    {
        $options = [
            'entity' => $this->entity,
            'fieldDefinitions' => new CsvField([
                'name' => 'highest_cost',
                'type' => 'aggregated(CsvMigrations\\Aggregator\\MaxAggregator,Foo,cost_amount)',
                'required' => false,
                'non-searchable' => false,
                'unique' => false
            ])
        ];

        $this->assertSame('2000.1', $this->renderer->provide(null, $options));
    }

    public function testProvideWithDisplayField()
    {
        $options = [
            'entity' => $this->entity,
            'fieldDefinitions' => new CsvField([
                'name' => 'highest_cost',
                'type' => 'aggregated(CsvMigrations\\Aggregator\\MaxAggregator,Foo,cost_amount,country)',
                'required' => false,
                'non-searchable' => false,
                'unique' => false
            ])
        ];

        $this->assertSame('Cyprus', $this->renderer->provide(null, $options));
    }
}

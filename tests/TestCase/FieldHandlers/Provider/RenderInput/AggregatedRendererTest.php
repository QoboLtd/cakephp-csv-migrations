<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderInput;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\Config\AggregatedConfig;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\Provider\ProviderInterface;
use CsvMigrations\FieldHandlers\Provider\RenderInput\AggregatedRenderer;

class AggregatedRendererTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.Foo',
        'plugin.CsvMigrations.Leads',
    ];

    private $renderer;
    private $entity;

    public function setUp(): void
    {
        $this->renderer = new AggregatedRenderer(new AggregatedConfig('aggregated', 'Leads'));
        $this->entity = TableRegistry::getTableLocator()->get('Leads')->get('00000000-0000-0000-0000-000000000001');
    }

    public function tearDown(): void
    {
        unset($this->entity);
        unset($this->renderer);
    }

    public function testInterface(): void
    {
        $this->assertInstanceOf(ProviderInterface::class, $this->renderer);
    }

    public function testProvide(): void
    {
        $options = [
            'entity' => $this->entity,
            'label' => 'Highest Cost',
            'fieldDefinitions' => new CsvField([
                'name' => 'highest_cost',
                'type' => 'aggregated(CsvMigrations\\Aggregator\\MaxAggregator,Foo,cost_amount)',
                'required' => false,
                'non-searchable' => false,
                'unique' => false,
            ]),
        ];

        $html = $this->renderer->provide(null, $options);

        $this->assertRegexp('/value="2000.1"/', $html);
        $this->assertRegexp('/disabled="disabled"/', $html);
    }

    public function testProvideWithoutEntity(): void
    {
        $options = [
            'entity' => null,
            'label' => 'Highest Cost',
            'fieldDefinitions' => new CsvField([
                'name' => 'highest_cost',
                'type' => 'aggregated(CsvMigrations\\Aggregator\\MaxAggregator,Foo,cost_amount,country)',
                'required' => false,
                'non-searchable' => false,
                'unique' => false,
            ]),
        ];

        $html = $this->renderer->provide(null, $options);

        $this->assertRegexp('/value=""/', $html);
        $this->assertRegexp('/disabled="disabled"/', $html);
    }
}

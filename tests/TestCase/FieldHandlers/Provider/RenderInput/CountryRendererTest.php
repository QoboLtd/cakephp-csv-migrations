<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\RenderInput;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\Config\CountryConfig;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\Provider\ProviderInterface;
use CsvMigrations\FieldHandlers\Provider\RenderInput\CountryRenderer;

class CountryRendererTest extends TestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.foo'
    ];

    /**
     * Renderer object
     */
    private $renderer;

    /**
     * Entity object
     */
    private $entity;

    /**
     * Setup method
     */
    public function setUp() : void
    {
        $this->renderer = new CountryRenderer(new CountryConfig('list'));
        $this->entity = TableRegistry::get('foo')->get('00000000-0000-0000-0000-000000000001');
    }

    /**
     * TearDown method
     */
    public function tearDown() : void
    {
        unset($this->entity);
        unset($this->renderer);
    }

    /**
     * Test provide method that is success
     */
    public function testProvide() : void
    {
        $options = [
            'entity' => $this->entity,
            'label' => 'Country',
            'fieldDefinitions' => new CsvField([
                'name' => 'country',
                'type' => 'country(countries)',
                'required' => false,
                'non-searchable' => false,
                'unique' => false
            ])
        ];

        $html = $this->renderer->provide(null, $options);

        $this->assertRegexp('/class=".*flag-icon.*"/', $html);
    }
}

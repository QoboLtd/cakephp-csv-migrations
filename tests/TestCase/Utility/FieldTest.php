<?php
namespace CsvMigrations\Test\TestCase\Utility;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\Utility\Field;

/**
 * CsvMigrations\Utility\Field Test Case
 */
class FieldTest extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown() : void
    {
        parent::tearDown();
    }

    public function testGetLookup() : void
    {
        $table = TableRegistry::get('Articles');

        $expected = ['name'];
        $this->assertSame($expected, Field::getLookup($table));
    }

    public function testGetLookupEmpty() : void
    {
        $table = TableRegistry::get('NonExistingTable');

        $this->assertSame([], Field::getLookup($table));
    }

    public function testGetCsv() : void
    {
        $table = TableRegistry::get('Articles');

        $result = Field::getCsv($table);
        $expected = ['id', 'name', 'status', 'author', 'main_article', 'category', 'image', 'created', 'modified'];
        $this->assertSame($expected, array_keys($result));

        foreach ($result as $csvField) {
            $this->assertInstanceOf(CsvField::class, $csvField);
        }
    }

    public function testGetCsvEmpty() : void
    {
        $table = TableRegistry::get('NonExistingTable');

        $this->assertSame([], Field::getCsv($table));
    }

    public function testGetCsvField() : void
    {
        /** @var \CsvMigrations\FieldHandlers\CsvField */
        $result = Field::getCsvField(TableRegistry::get('Articles'), 'name');

        $this->assertInstanceOf(CsvField::class, $result);
        $this->assertSame('name', $result->getName());
        $this->assertSame('string', $result->getType());
    }

    public function testGetCsvFieldWithInvalidField() : void
    {
        $result = Field::getCsvField(TableRegistry::get('Articles'), 'non-existing-field');

        $this->assertNull($result);
    }

    public function testGetVirtual() : void
    {
        $this->assertSame(['name' => ['foo', 'bar']], Field::getVirtual(TableRegistry::get('Foo')));
    }

    public function testGetVirtualEmpty() : void
    {
        $this->assertSame([], Field::getVirtual(TableRegistry::get('Articles')));
    }

    public function testGetCsvView() : void
    {
        $expected = [
            ['Details', 'name', 'status'],
            ['Details', 'author', '']
        ];

        $this->assertSame($expected, Field::getCsvView(TableRegistry::get('Articles'), 'add'));
    }

    public function testGetCsvViewWithIncludePluginModel() : void
    {
        $expected = [
            [
                ['plugin' => null, 'model' => 'Articles', 'name' => 'Details'],
                ['plugin' => null, 'model' => 'Articles', 'name' => 'name'],
                ['plugin' => null, 'model' => 'Articles', 'name' => 'status'],
            ],
            [
                ['plugin' => null, 'model' => 'Articles', 'name' => 'Details'],
                ['plugin' => null, 'model' => 'Articles', 'name' => 'author'],
                ['plugin' => null, 'model' => 'Articles', 'name' => '']
            ]
        ];

        $this->assertSame($expected, Field::getCsvView(TableRegistry::get('Articles'), 'add', true));
    }

    public function testGetCsvViewWithArrangeInPanels() : void
    {
        $expected = [
            'Details' => [
                ['name', 'status'],
                ['author', '']
            ]
        ];

        $this->assertSame($expected, Field::getCsvView(TableRegistry::get('Articles'), 'add', false, true));
    }

    public function testGetCsvViewWithIncludePluginModelAndArrangeInPanels() : void
    {
        $expected = [
            'Details' => [
                [
                    ['plugin' => null, 'model' => 'Articles', 'name' => 'name'],
                    ['plugin' => null, 'model' => 'Articles', 'name' => 'status'],
                ],
                [
                    ['plugin' => null, 'model' => 'Articles', 'name' => 'author'],
                    ['plugin' => null, 'model' => 'Articles', 'name' => '']
                ]
            ]
        ];

        $this->assertSame($expected, Field::getCsvView(TableRegistry::get('Articles'), 'add', true, true));
    }

    public function testGetCsvViewEmpty() : void
    {
        $this->assertSame([], Field::getCsvView(TableRegistry::get('NonExistingTable'), 'add', true, true));
    }

    public function testGetList() : void
    {
        $expected = [
            'one' => [
                'label' => 'One',
                'inactive' => false
            ],
            'two' => [
                'label' => 'Two',
                'inactive' => false
            ]
        ];

        $this->assertSame($expected, Field::getList('list'));
    }

    public function testGetListWithModule() : void
    {
        $expected = [
            'one' => [
                'label' => 'One',
                'inactive' => false
            ],
            'two' => [
                'label' => 'Two',
                'inactive' => false
            ]
        ];

        $this->assertSame($expected, Field::getList('Common.list'));
    }

    public function testGetListWithChildren() : void
    {
        $expected = [
            'first_level_1' => [
                'label' => 'First level 1',
                'inactive' => false,
                'children' => [
                    'first_level_1.second_level_1' => [
                        'label' => 'Second level 1',
                        'inactive' => false,
                        'children' => [
                            'first_level_1.second_level_1.third_level_1' => [
                                'label' => 'Third level 1',
                                'inactive' => false
                            ]
                        ]
                    ]
                ]
            ],
            'first_level_2' => [
                'label' => 'First level 2',
                'inactive' => false
            ]
        ];

        $this->assertSame($expected, Field::getList('nested'));
    }

    public function testGetListWithChildrenFlatten() : void
    {
        $expected = [
            'first_level_1' => [
                'label' => 'First level 1',
                'inactive' => false,
            ],
            'first_level_1.second_level_1' => [
                'label' => 'Second level 1',
                'inactive' => false,
            ],
            'first_level_1.second_level_1.third_level_1' => [
                'label' => 'Third level 1',
                'inactive' => false
            ],
            'first_level_2' => [
                'label' => 'First level 2',
                'inactive' => false
            ]
        ];

        $this->assertSame($expected, Field::getList('nested', true));
    }

    public function testGetListEmpty() : void
    {
        $this->assertSame([], Field::getList('non-existing-list'));
    }
}

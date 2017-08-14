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
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    public function testGetLookup()
    {
        $table = TableRegistry::get('Articles');

        $expected = ['name'];
        $this->assertSame($expected, Field::getLookup($table));
    }

    public function testGetLookupEmpty()
    {
        $table = TableRegistry::get('NonExistingTable');

        $this->assertSame([], Field::getLookup($table));
    }

    public function testGetCsv()
    {
        $table = TableRegistry::get('Articles');

        $result = Field::getCsv($table);
        $expected = ['id', 'name', 'status', 'author', 'category'];
        $this->assertSame($expected, array_keys($result));

        foreach ($result as $csvField) {
            $this->assertInstanceOf(CsvField::class, $csvField);
        }
    }

    public function testGetCsvEmpty()
    {
        $table = TableRegistry::get('NonExistingTable');

        $this->assertSame([], Field::getCsv($table));
    }

    public function testGetList()
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

    public function testGetListWithChildren()
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

    public function testGetListWithChildrenFlatten()
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
}

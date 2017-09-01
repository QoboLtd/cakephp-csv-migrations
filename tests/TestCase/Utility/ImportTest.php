<?php
namespace CsvMigrations\Test\TestCase\Utility;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\Utility\Import;

/**
 * CsvMigrations\Utility\Import Test Case
 */
class ImportTest extends TestCase
{
    public $fixtures = [
        'plugin.csv_migrations.import_results'
    ];

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

    public function testToDatatables()
    {
        $table = TableRegistry::get('CsvMigrations.ImportResults');
        $query = $table->find();

        $columns = ['row_number', 'status', 'status_message'];

        $expected = [
            [1, 'Success', 'Lorem ipsum dolor sit amet, aliquet feugiat.'],
            [1, 'Fail', 'Lorem ipsum dolor sit amet, aliquet feugiat.'],
            [2, 'Pending', 'Lorem ipsum dolor sit amet, aliquet feugiat.'],
            [3, 'Success', 'Lorem ipsum dolor sit amet, aliquet feugiat.']
        ];
        $result = Import::toDatatables($query->all(), $columns);

        $this->assertSame($expected, $result);
    }

    public function testActionButtons()
    {
        $articlesTable = TableRegistry::get('CsvMigrations.Articles');
        $table = TableRegistry::get('CsvMigrations.ImportResults');
        $query = $table->find();

        $columns = ['row_number', 'status', 'status_message'];
        $data = Import::toDatatables($query->all(), $columns);

        $expected = '/csv-migrations/articles/view/8aab326f-59ba-4222-9d76-dbe964a2302d';

        $result = Import::actionButtons($query->all(), $articlesTable, $data);

        $this->assertContains($expected, end($result[0]));
    }

    public function testSetStatusLabels()
    {
        $articlesTable = TableRegistry::get('CsvMigrations.Articles');
        $table = TableRegistry::get('CsvMigrations.ImportResults');
        $query = $table->find();

        $columns = ['row_number', 'status', 'status_message'];
        $data = Import::toDatatables($query->all(), $columns);

        $expected = '<span class="label label-success">Success</span>';

        $result = Import::setStatusLabels($data, 1);

        $this->assertContains($expected, $result[0][1]);
    }
}

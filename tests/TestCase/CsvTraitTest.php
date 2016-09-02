<?php
namespace CsvMigrations\Test\TestCase;

use League\Csv\Reader;
use PHPUnit_Framework_TestCase;

/**
 * CsvTrait Test
 *
 * Traits are fun to test.  See this, for example:
 *
 * http://stackoverflow.com/a/31124840/151647
 *
 * Which references PHPUnit's way of testing abstract
 * things:
 *
 * https://phpunit.de/manual/current/en/test-doubles.html#test-doubles.mocking-traits-and-abstract-classes
 *
 * Our case is even more fun, as CsvTrait provides only
 * protected methods and loading any existing class that
 * uses this trait is even more complicated.
 *
 * So ...
 *
 * NOTE: To make testing simpler, methods were copied over
 *       from the CsvTrait into this test class and called
 *       directly.  Once the migration to league/csv is done
 *       we can remove this whole shenanigan and work on
 *       decoupling our code, so that it could be tested
 *       properly.
 */
class CsvTraitTest extends PHPUnit_Framework_TestCase
{
    /**
     * Old implementation of CsvViewComponent::_getCsvData()
     */
    protected function oldCompGetCsvData($path)
    {
        $result = [];
        if (file_exists($path)) {
            if (false !== ($handle = fopen($path, 'r'))) {
                $row = 0;
                while (false !== ($data = fgetcsv($handle, 0, ','))) {
                    // skip first row
                    if (0 === $row) {
                        $row++;
                        continue;
                    }
                    $result[] = $data;
                }
                fclose($handle);
            }
        }

        return $result;
    }

    /**
     * New implementation of CsvViewComponent::_getCsvData()
     */
    protected function newCompGetCsvData($path)
    {
        $reader = Reader::createFromPath($path);
        $result = $reader->setOffset(1)->fetchAll();

        return $result;
    }

    /**
     * New implementation of CsvTrait::_getCsvData()
     */
    protected function newGetCsvData($path, $skipHeaders = true)
    {
        $offset = $skipHeaders ? 1 : 0;
        $reader = Reader::createFromPath($path);
        $result = $reader->setOffset($offset)->fetchAll();

        return $result;
    }

    /**
     * Old implementation of CsvTrait::_getCsvData()
     */
    protected function oldGetCsvData($path, $skipHeaders = true)
    {
        $result = [];
        if (file_exists($path)) {
            if (false !== ($handle = fopen($path, 'r'))) {
                $row = 0;
                while (false !== ($data = fgetcsv($handle, 0, ','))) {
                    // skip csv headers
                    if ($skipHeaders && 0 === $row) {
                        $row++;
                        continue;
                    }
                    $result[] = $data;
                }
                fclose($handle);
            }
        }

        return $result;
    }

    /**
     * Old implementation of CsvTrait::_prepareCsvData()
     */
    protected function oldPrepareCsvData(array $csvData)
    {
        /*
         * Field parameters. Order is important.
         * @var array
         */
        $defaultParams = [
           'name' => '',
           'type' => '',
           'required' => '',
           'non-searchable' => '',
           'unique' => false,
        ];

        $result = [];
        foreach ($csvData as $col) {
            $fields = array_keys($defaultParams);
            $namedCol = [];
            foreach ($fields as $i => $field) {
                if (!empty($col[$i])) {
                    $namedCol[$field] = $col[$i];
                }
            }
            $namedCol = array_merge($defaultParams, $namedCol);
            $result[$namedCol['name']] = $namedCol;
        }

        return $result;
    }

    /**
     * New implementation of CsvTrait::_prepareCsvData()
     *
     * NOTE: Default values are NOT applied. This should either
     *       be fixed, or dropped.  If all the default values
     *       are nothing, like false/empty string/null/etc, then
     *       we can avoid them altogether as league/csv sets
     *       them to null when using fetchAssoc() with keys.
     */
    protected function newPrepareCsvData($path)
    {
        /*
         * Field parameters. Order is important.
         * @var array
         */
        $defaultParams = [
           'name' => '',
           'type' => '',
           'required' => '',
           'non-searchable' => '',
           'unique' => false,
        ];

        $reader = Reader::createFromPath($path);
        $rows = $reader->setOffset(1)->fetchAssoc(array_keys($defaultParams));
        $result = [];
        foreach ($rows as $row) {
            $result[$row['name']] = $row;
        }

        return $result;
    }

    /**
     * Temporary test of CsvTrait::_getCsvData()
     *
     * This test is here temporarily just for the
     * migration from our CSV parsing implementation
     * to that one of the league/csv.
     *
     * It makes sure that new implementation returns
     * exactly the same results as the old one.
     */
    public function testGetCsvData()
    {
        $file = dirname(__DIR__) . DS . 'data' . DS . 'CsvMigrations' . DS . 'migrations' . DS . 'Foo' . DS . 'migration.dist.csv';

        // Call with default value for $skipHeaders
        $oldResult = $this->oldGetCsvData($file);
        $newResult = $this->newGetCsvData($file);
        $this->assertEquals($oldResult, $newResult, "New CSV parsing returns different result from the old one");

        // Call with $skipHeaders enabled
        $oldResult = $this->oldGetCsvData($file, true);
        $newResult = $this->newGetCsvData($file, true);
        $this->assertEquals($oldResult, $newResult, "New CSV parsing without headers returns different result from the old one");

        // Call with $skipHeaders disabled
        $oldResult = $this->oldGetCsvData($file, false);
        $newResult = $this->newGetCsvData($file, false);
        $this->assertEquals($oldResult, $newResult, "New CSV parsing with headers returns different result from the old one");
    }

    /**
     * Temporary test of CsvTrait::_prepareCsvData()
     *
     * This test is here temporarily just for the
     * migration from our CSV parsing implementation
     * to that one of the league/csv.
     *
     * It makes sure that new implementation returns
     * exactly the same results as the old one.
     */
    public function testPrepareCsvData()
    {
        $file = dirname(__DIR__) . DS . 'data' . DS . 'CsvMigrations' . DS . 'migrations' . DS . 'Foo' . DS . 'migration.dist.csv';

        // Call with default value for $skipHeaders
        $oldResult = $this->oldPrepareCsvData($this->oldGetCsvData($file));
        $newResult = $this->newPrepareCsvData($file);
        $this->assertEquals($oldResult, $newResult, "New CSV preparation returns different result from the old one");
    }

    /**
     * Temporary test of CsvViewComponent::_getCsvData()
     *
     * This test is here temporarily just for the
     * migration from our CSV parsing implementation
     * to that one of the league/csv.
     *
     * It makes sure that new implementation returns
     * exactly the same results as the old one.
     */
    public function testCompGetCsvData()
    {
        // View with panels (add/edit/view)
        $file = dirname(__DIR__) . DS . 'data' . DS . 'CsvMigrations' . DS . 'views' . DS . 'Foo' . DS . 'view.csv';

        $oldResult = $this->oldCompGetCsvData($file);
        $newResult = $this->newCompGetCsvData($file);

        $this->assertEquals($oldResult, $newResult, "New view CSV parsing returns different result from the old one");

        // View without panels (index)
        $file = dirname(__DIR__) . DS . 'data' . DS . 'CsvMigrations' . DS . 'views' . DS . 'Foo' . DS . 'index.csv';

        $oldResult = $this->oldCompGetCsvData($file);
        $newResult = $this->newCompGetCsvData($file);

        $this->assertEquals($oldResult, $newResult, "New view CSV parsing returns different result from the old one");
    }
}

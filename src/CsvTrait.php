<?php
namespace CsvMigrations;

use Cake\Core\Configure;

trait CsvTrait
{
    /**
     * Field parameters. Order is important.
     * @var array
     */
    private $__defaultParams = [
        'name' => '',
        'type' => '',
        'required' => '',
        'non-searchable' => ''
    ];

    /**
     * Method that restructures csv data for better handling and searching through.
     *
     * @param  array  $csvData csv data
     * @return array
     */
    protected function _prepareCsvData(array $csvData)
    {
        $result = [];
        foreach ($csvData as $col) {
            $fields = array_keys($this->__defaultParams);
            $namedCol = array();
            foreach ($fields as $i => $field) {
                if (!empty($col[$i])) {
                    $namedCol[$field] = $col[$i];
                }
            }
            $namedCol = array_merge($this->__defaultParams, $namedCol);
            $result[$namedCol['name']] = $namedCol;
        }

        return $result;
    }

    /**
     * Method that retrieves csv file data.
     *
     * @param  string $path     csv file path
     * @param  array  $skipRows which rows to skip
     * @return array            csv data
     * @todo this method should be moved to a Trait class as is used throught Csv Migrations and Csv Views plugins
     */
    protected function _getCsvData($path, array $skipRows = [0])
    {
        $result = [];
        if (file_exists($path)) {
            if (false !== ($handle = fopen($path, 'r'))) {
                $row = 0;
                while (false !== ($data = fgetcsv($handle, 0, ','))) {
                    // skip first row
                    if (in_array($row, $skipRows)) {
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
}

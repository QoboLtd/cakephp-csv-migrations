<?php
namespace CsvMigrations;

use Cake\Core\Configure;

trait CsvTrait
{
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
            $col += array_values($this->_defaultParams);
            $result[$col[0]] = array_combine(array_keys($this->_defaultParams), $col);
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

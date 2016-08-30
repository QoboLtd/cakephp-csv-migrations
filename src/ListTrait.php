<?php
namespace CsvMigrations;

use Cake\Collection\Collection;
use Cake\Core\Configure;
use CsvMigrations\CsvTrait;

trait ListTrait
{
    use CsvTrait;

    /**
     * Field parameters
     * @var array
     */
    protected $_fieldParams = ['value', 'label', 'inactive'];

    /**
     * Method that retrieves select input options by list name.
     *
     * @param  string $listName list name
     * @return array            list options
     */
    protected function _getSelectOptions($listName)
    {
        $result = $this->__getListFieldOptions($listName);
        $result = $this->__filterOptions($result);

        /*
        nested list options
         */
        $collection = new Collection($result);
        $result = $collection->listNested()->printer('name', 'id', ' - ')->toArray();

        return $result;
    }

    /**
     * Method that retrieves list field options.
     *
     * @param  string $listName list name
     * @param  string $prefix   nested option prefix
     * @return array
     */
    private function __getListFieldOptions($listName, $prefix = null)
    {
        $result = [];
        $path = Configure::readOrFail('CsvMigrations.lists.path') . $listName . '.csv';
        $listData = $this->_getCsvData($path);
        if (!empty($listData)) {
            $result = $this->__prepareListOptions($listData, $listName, $prefix);
        }

        return $result;
    }

    /**
     * Method that filters list options, excluding non-active ones
     *
     * @param  array  $options list options
     * @param  int    $index nested list index
     * @param  string $parent parent id
     * @return array
     */
    private function __filterOptions($options, $index = -1, $parent = null)
    {
        $result = [];
        foreach ($options as $k => $v) {
            if ($v['inactive']) {
                continue;
            }
            $index++;
            $result[$index] = ['id' => $k, 'parent_id' => $parent, 'name' => $v['label']];
            /*
            iterate over children options
             */
            if (isset($v['children'])) {
                $result[$index]['children'] = $this->__filterOptions($v['children'], $index, $k);
            }
        }

        return $result;
    }

    /**
     * Method that restructures list options csv data for better handling.
     *
     * @param  array  $data     csv data
     * @param  string $listName list name
     * @param  string $prefix   nested option prefix
     * @return array
     * @todo   Validation of CVS files should probably be done separately, elsewhere.
     *         Note: the number of columns can vary per record.
     */
    private function __prepareListOptions($data, $listName, $prefix = null)
    {
        $result = [];
        $paramsCount = count($this->_fieldParams);

        foreach ($data as $row) {
            $colCount = count($row);
            if ($colCount !== $paramsCount) {
                throw new \RuntimeException(sprintf($this->_errorMessages[__FUNCTION__], $colCount, $paramsCount));
            }
            $field = array_combine($this->_fieldParams, $row);

            $result[$prefix . $field['value']] = [
                'label' => $field['label'],
                'inactive' => (bool)$field['inactive']
            ];

            /*
            get child options
             */
            $children = $this->__getListFieldOptions($listName . DS . $field['value'], $prefix . $field['value'] . '.');
            if (!empty($children)) {
                $result[$prefix . $field['value']]['children'] = $children;
            }
        }

        return $result;
    }
}

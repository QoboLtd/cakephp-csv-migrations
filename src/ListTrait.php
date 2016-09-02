<?php
namespace CsvMigrations;

use Cake\Collection\Collection;
use Cake\Core\Configure;
use CsvMigrations\Parser\Csv\ListParser;

trait ListTrait
{
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
        $listData = [];

        // ListParser does its own check for whether or not the
        // file is_readable(), and if not - throws an exception.
        // In this particular case though, we are called recursively,
        // to fetch child list items, if any.  Before we attempt to
        // get those, it's good to check if they exist.
        //
        // This can also be implemented with try/catch, but there
        // might be more reasons for exceptions during parsing, so
        // this check is the easiest approach.
        if (is_readable($path)) {
            $parser = new ListParser();
            $listData = $parser->parseFromPath($path);
        }

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

        foreach ($data as $field) {
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

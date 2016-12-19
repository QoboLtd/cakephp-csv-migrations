<?php
namespace CsvMigrations;

use Cake\Collection\Collection;
use Cake\Core\Configure;
use CsvMigrations\Parser\Csv\ListParser;
use CsvMigrations\PathFinder\ListPathFinder;

trait ListTrait
{
    /**
     * Method that retrieves select input options by list name.
     *
     * @param string $listName list name
     * @param string $spacer The string to use for prefixing the values according to
     * their depth in the tree
     * @param bool $flatten flat list flag
     * @return array list options
     */
    protected function _getSelectOptions($listName, $spacer = ' - ', $flatten = true)
    {
        $result = $this->__getListFieldOptions($listName);
        $result = $this->__filterOptions($result);

        if (!$flatten) {
            return $result;
        }

        // flatten list options
        $collection = new Collection($result);
        $result = $collection->listNested()->printer('name', 'id', $spacer)->toArray();

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

        $listData = [];
        try {
            $pathFinder = new ListPathFinder;
            $path = $pathFinder->find(null, $listName);
            $parser = new ListParser();
            $listData = $parser->parseFromPath($path);
        } catch (\Exception $e) {
            /* Do nothing.
             *
             * ListPathFinder and ListParser check for the
             * file to exist and to be readable and so on,
             * but here we do load lists recursively (for
             * sub-lists, etc), which might result in files
             * not always being there.
             *
             * In this particular case, it's not the end of the
             * world.
             */
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

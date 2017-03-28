<?php
namespace CsvMigrations\FieldHandlers;

use Cake\Collection\Collection;
use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\BaseListFieldHandler;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * BaseCsvListFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to list field handlers, which rely on CSV
 * files and use dot notation to store nested values.
 */
abstract class BaseCsvListFieldHandler extends BaseListFieldHandler
{
    /**
     * HTML to add to invalid list items
     */
    const VALUE_NOT_FOUND_HTML = '%s <span class="text-danger glyphicon glyphicon-exclamation-sign" title="Invalid list item" aria-hidden="true"></span>';

    /**
     * Format field value
     *
     * This method provides a customization point for formatting
     * of the field value before rendering.
     *
     * NOTE: The value WILL NOT be sanitized during the formatting.
     *       It is assumed that sanitization happens either before
     *       or after this method is called.
     *
     * @param mixed $data    Field value data
     * @param array $options Field formatting options
     * @return string
     */
    protected function formatValue($data, array $options = [])
    {
        $result = '';

        if (empty($data)) {
            return $result;
        }

        $fieldOptions = $this->_getSelectOptions($options['fieldDefinitions']->getLimit());

        if (isset($fieldOptions[$data])) {
            // Concatenate all parents together with value
            $parents = explode('.', $data);
            if (!empty($parents)) {
                $path = '';
                foreach ($parents as $parent) {
                    $path = empty($path) ? $parent : $path . '.' . $parent;
                    if (isset($fieldOptions[$path])) {
                        $result .= $fieldOptions[$path];
                    }
                }
            }
        } else {
            if (isset($options['renderAs']) && $options['renderAs'] === static::RENDER_PLAIN_VALUE) {
                $result = $data;
            } else {
                $result = sprintf(static::VALUE_NOT_FOUND_HTML, $data);
            }
        }

        return $result;
    }

    /**
     * Get options for field search
     *
     * This method prepares an array of search options, which includes
     * label, form input, supported search operators, etc.  The result
     * can be controlled with a variety of options.
     *
     * @param  array  $options Field options
     * @return array           Array of field input HTML, pre and post CSS, JS, etc
     */
    public function getSearchOptions(array $options = [])
    {
        // Fix options as early as possible
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $result = parent::getSearchOptions($options);
        if (empty($result[$this->field]['input'])) {
            return $result;
        }

        $content = $this->cakeView->Form->select(
            '{{name}}',
            $this->_getSelectOptions($options['fieldDefinitions']->getLimit()),
            [
                'label' => false
            ]
        );

        $result[$this->field]['input'] = [
            'content' => $content
        ];

        return $result;
    }

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

        $module = null;
        if (strpos($listName, '.') !== false) {
            list($module, $listName) = explode('.', $listName, 2);
        }
        $listData = [];
        try {
            $mc = new ModuleConfig(ModuleConfig::CONFIG_TYPE_LIST, $module, $listName);
            $listData = $mc->parse();
        } catch (\Exception $e) {
            /* Do nothing.
             *
             * ModuleConfig checks for the
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

<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CsvMigrations\FieldHandlers\Provider\RenderValue;

use InvalidArgumentException;

/**
 * ListRenderer
 *
 * Render value as list item
 */
class ListRenderer extends AbstractRenderer
{
    /**
     * HTML to add to invalid list items
     */
    const VALUE_NOT_FOUND_HTML = '%s <span class="text-danger glyphicon glyphicon-exclamation-sign" title="Invalid list item" aria-hidden="true"></span>';

    /**
     * Provide rendered value
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $result = '';
        $data = (string)$data;

        if (empty($data)) {
            return $result;
        }

        if (empty($options['listItems']) && empty($options['fieldDefinitions'])) {
            throw new InvalidArgumentException("No listItems or fieldDefinitions options provided");
        }

        if (empty($options['listItems'])) {
            $selectListItems = $this->config->getProvider('selectOptions');
            $selectListItems = new $selectListItems($this->config);
            $listName = $options['fieldDefinitions']->getLimit();
            $listOptions = [];

            $options['listItems'] = $selectListItems->provide($listName, $listOptions);
        }

        $listItems = $options['listItems'];

        if (!is_array($options['listItems'])) {
            throw new InvalidArgumentException("List items is not an array");
        }

        if (!isset($listItems[$data])) {
            return sprintf(static::VALUE_NOT_FOUND_HTML, parent::provide($data, $options));
        }

        // Concatenate all parents together with value
        // Nested values are dot-separated.  At least the
        // value itself will be included, if no parents.
        $parents = explode('.', $data);
        $path = '';
        foreach ($parents as $parent) {
            $path = empty($path) ? $parent : $path . '.' . $parent;
            $result .= isset($listItems[$path]) ? $listItems[$path] : '';
        }

        return $result;
    }
}

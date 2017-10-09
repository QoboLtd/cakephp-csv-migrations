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
namespace CsvMigrations\FieldHandlers\Renderer;

use InvalidArgumentException;

/**
 * ListRenderer
 *
 * Render value as list item
 */
class ListRenderer extends BaseRenderer
{
    /**
     * HTML to add to invalid list items
     */
    const VALUE_NOT_FOUND_HTML = '%s <span class="text-danger glyphicon glyphicon-exclamation-sign" title="Invalid list item" aria-hidden="true"></span>';

    /**
     * Render value
     *
     * @throws \InvalidArgumentException when listItems option is not an array
     * @param mixed $value Value to render
     * @param array $options Rendering options
     * @return string Text, HTML or other string result
     */
    public function renderValue($value, array $options = [])
    {
        $result = '';
        $value = (string)$value;

        // No known list items, so render value as safe string
        if (!isset($options['listItems'])) {
            return parent::renderValue($value, $options);
        }

        // TODO : Change to iterator check instead for more flexibility
        if (!is_array($options['listItems'])) {
            throw new InvalidArgumentException("Provided list items are not an array");
        }

        $listItems = $options['listItems'];
        if (!isset($listItems[$value])) {
            return sprintf(static::VALUE_NOT_FOUND_HTML, parent::renderValue($value, $options));
        }

        // Concatenate all parents together with value
        // Nested values are dot-separated.  At least the
        // value itself will be included, if no parents.
        $parents = explode('.', $value);
        $path = '';
        foreach ($parents as $parent) {
            $path = empty($path) ? $parent : $path . '.' . $parent;
            $result .= isset($listItems[$path]) ? $listItems[$path] : '';
        }

        return $result;
    }
}

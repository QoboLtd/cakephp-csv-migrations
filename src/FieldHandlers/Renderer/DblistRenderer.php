<?php
namespace CsvMigrations\FieldHandlers\Renderer;

use InvalidArgumentException;

/**
 * DblistRenderer
 *
 * Render value as database list item
 */
class DblistRenderer extends BaseRenderer
{
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

        // No known list name, so render value as safe string
        if (empty($options['listName'])) {
            return parent::renderValue($value, $options);
        }

        $listName = (string)$options['listName'];
        $result = (string)$this->view->cell('CsvMigrations.Dblist::renderValue', [$value, $listName])->render('renderValue');

        return $result;
    }
}

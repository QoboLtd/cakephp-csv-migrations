<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class EmailFieldHandler extends BaseFieldHandler
{
    /**
     * Method that renders default type field's value.
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = $this->cakeView->Html->link($data, 'mailto:' . $data, ['target' => '_blank']);

        return $result;
    }
}

<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class UrlFieldHandler extends BaseFieldHandler
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
        $result = filter_var($data, FILTER_SANITIZE_URL);

        // Only link to URLs with schema, to avoid unpredictable behavior
        if (!empty($result) && filter_var($result, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
            $result = $this->cakeView->Html->link($result, $result, ['target' => '_blank']);
        }

        return $result;
    }
}

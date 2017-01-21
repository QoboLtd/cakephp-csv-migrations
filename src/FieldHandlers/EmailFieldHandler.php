<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class EmailFieldHandler extends BaseFieldHandler
{
    /**
     * Method that renders default type field's value.
     *
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($data, array $options = [])
    {
        $result = filter_var($data, FILTER_SANITIZE_EMAIL);

        // Only link to valid emails, to avoid unpredictable behavior
        if (!empty($result) && filter_var($result, FILTER_VALIDATE_EMAIL)) {
            if (!isset($options['renderAs']) || !$options['renderAs'] === static::RENDER_PLAIN_VALUE) {
                $result = $this->cakeView->Html->link($result, 'mailto:' . $result, ['target' => '_blank']);
            }
        }

        return $result;
    }
}

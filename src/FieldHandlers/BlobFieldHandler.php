<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseSimpleFieldHandler;
use Phinx\Db\Adapter\MysqlAdapter;

class BlobFieldHandler extends BaseSimpleFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'blob';

    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'textarea';

    /**
     * Convert CsvField to one or more DbField instances
     *
     * Simple fields from migrations CSV map one-to-one to
     * the database fields.  More complex fields can combine
     * multiple database fields for a single CSV entry.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array                                           DbField instances
     */
    public static function fieldToDb(CsvField $csvField)
    {
        $csvField->setType(self::DB_FIELD_TYPE);
        // Set the limit to Phinx\Db\Adapter\MysqlAdapter::BLOB_LONG
        $csvField->setLimit(MysqlAdapter::BLOB_LONG);

        $dbField = DbField::fromCsvField($csvField);
        $result = [
            $csvField->getName() => $dbField,
        ];

        return $result;
    }

    /**
     * Render field input
     *
     * This method prepares the form input for the given field,
     * including the input itself, label, pre-populated value,
     * and so on.  The result can be controlled via the variety
     * of options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field input HTML
     */
    public function renderInput($data = '', array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $data = $this->_getFieldValueFromData($data);
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }

        return parent::renderInput($data, $options);
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

        $content = $this->cakeView->Form->input('{{name}}', [
            'value' => '{{value}}',
            'type' => 'text',
            'label' => false
        ]);

        $result[$this->field]['input'] = [
            'content' => $content,
        ];

        return $result;
    }
}

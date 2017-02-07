<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseStringFieldHandler;
use Phinx\Db\Adapter\MysqlAdapter;

class TextFieldHandler extends BaseStringFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'text';

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
        // Set the limit to Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG
        $csvField->setLimit(MysqlAdapter::TEXT_LONG);

        $dbField = DbField::fromCsvField($csvField);
        $result = [
            $csvField->getName() => $dbField,
        ];

        return $result;
    }

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
        $result = (string)$data;

        if (empty($result)) {
            return $result;
        }

        if (array_key_exists('renderAs', $options) && ($options['renderAs'] === static::RENDER_PLAIN_VALUE)) {
            return $result;
        }

        // Auto-paragraph
        $result = $this->cakeView->Text->autoParagraph($result);

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

        $content = $this->cakeView->Form->input('{{name}}', [
            'value' => '{{value}}',
            'type' => 'text',
            'label' => false
        ]);

        $result[$this->field]['input'] = [
            'content' => $content
        ];

        return $result;
    }
}

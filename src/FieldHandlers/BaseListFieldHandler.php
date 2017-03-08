<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

/**
 * BaseListFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to list field handlers.
 */
abstract class BaseListFieldHandler extends BaseFieldHandler
{
    /**
     * Empty option label for select inputs
     */
    const EMPTY_OPTION_LABEL = ' -- Please choose -- ';

    /**
     * Search operators
     *
     * @var array
     */
    public $searchOperators = [
        'is' => [
            'label' => 'is',
            'operator' => 'IN',
        ],
        'is_not' => [
            'label' => 'is not',
            'operator' => 'NOT IN',
        ],
    ];

    /**
     * Render field value
     *
     * This method prepares the output of the value for the given
     * field.  The result can be controlled via the variety of
     * options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field value
     */
    public function renderValue($data, array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $result = parent::renderValue($data, $options);

        if (!empty($options['renderAs']) && static::RENDER_PLAIN_VALUE === $options['renderAs']) {
            return $result;
        }

        $result = $this->formatValue($result, $options);

        return $result;
    }

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
        $csvField->setType(static::DB_FIELD_TYPE);
        $csvField->setLimit(null);

        $dbField = DbField::fromCsvField($csvField);
        $result = [
            $csvField->getName() => $dbField,
        ];

        return $result;
    }
}

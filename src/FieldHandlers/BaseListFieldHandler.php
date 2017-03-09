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

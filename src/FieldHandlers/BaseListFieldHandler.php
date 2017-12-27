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
namespace CsvMigrations\FieldHandlers;

/**
 * BaseListFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to list field handlers.
 */
abstract class BaseListFieldHandler extends BaseFieldHandler
{
    /**
     * @var string $defaultConfigClass Config class to use as default
     */
    protected static $defaultConfigClass = '\\CsvMigrations\\FieldHandlers\\Provider\\Config\\ListConfig';

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

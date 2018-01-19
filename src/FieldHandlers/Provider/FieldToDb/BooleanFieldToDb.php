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
namespace CsvMigrations\FieldHandlers\Provider\FieldToDb;

/**
 * BooleanFieldToDb
 *
 * Boolean FieldToDb provides the conversion functionality
 * for boolean fields.
 */
class BooleanFieldToDb extends AbstractFieldToDb
{
    /**
     * @var string $dbFieldType Database field type
     */
    protected $dbFieldType = 'boolean';
}

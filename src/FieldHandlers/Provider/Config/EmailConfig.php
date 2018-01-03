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
namespace CsvMigrations\FieldHandlers\Provider\Config;

/**
 * EmailConfig
 *
 * This class provides the predefined configuration
 * for email field handlers.
 */
class EmailConfig extends FixedConfig
{
    /**
     * @var array $config Field handler configuration
     */
    protected $config = [
        'combinedFields' => '\\CsvMigrations\\FieldHandlers\\Provider\\CombinedFields\\NullCombinedFields',
        'fieldValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldValue\\MixedFieldValue',
        'fieldToDb' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldToDb\\StringFieldToDb',
        'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
        'searchOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOptions\\EmailSearchOptions',
        'selectOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SelectOptions\\NullSelectOptions',
        'inputRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderInput\\EmailRenderer',
        'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\EmailRenderer',
        'nameRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
    ];
}

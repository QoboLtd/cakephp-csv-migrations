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
 * BooleanConfig
 *
 * This class provides the predefined configuration
 * for boolean field handlers.
 */
class BooleanConfig extends FixedConfig
{
    /**
     * @var array $config Field handler configuration
     */
    protected $config = [
        'combinedFields' => '\\CsvMigrations\\FieldHandlers\\Provider\\CombinedFields\\NullCombinedFields',
        'fieldValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldValue\\MixedFieldValue',
        'fieldToDb' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldToDb\\BooleanFieldToDb',
        'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\BooleanSearchOperators',
        'searchOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOptions\\BooleanSearchOptions',
        'selectOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SelectOptions\\NullSelectOptions',
        'inputRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderInput\\BooleanRenderer',
        'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\BooleanYesNoRenderer',
        'nameRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
    ];
}

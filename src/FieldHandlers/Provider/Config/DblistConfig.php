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
 * DblistConfig
 *
 * This class provides the predefined configuration
 * for Dblist field handlers.
 */
class DblistConfig extends FixedConfig
{
    /**
     * @var array $config Field handler configuration
     */
    protected $config = [
        'combinedFields' => '\\CsvMigrations\\FieldHandlers\\Provider\\CombinedFields\\NullCombinedFields',
        'fieldValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldValue\\MixedFieldValue',
        'fieldToDb' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldToDb\\ListFieldToDb',
        'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\ListSearchOperators',
        'searchOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOptions\\DblistSearchOptions',
        'inputRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderInput\\DblistRenderer',
        'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\DblistRenderer',
        'nameRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
    ];
}

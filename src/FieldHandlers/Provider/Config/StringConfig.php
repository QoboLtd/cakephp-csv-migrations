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
 * StringConfig
 *
 * This class provides the predefined configuration
 * for string field handlers.
 */
class StringConfig extends FixedConfig
{
    /**
     * @var array $config Field handler configuration
     */
    protected $config = [
        'fieldValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldValue\\MixedFieldValue',
        'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\StringSearchOperators',
        'searchOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOptions\\StringSearchOptions',
        'selectOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SelectOptions\\NullSelectOptions',
        'inputRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderInput\\StringRenderer',
        'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\StringRenderer',
        'nameRenderAs' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
    ];
}

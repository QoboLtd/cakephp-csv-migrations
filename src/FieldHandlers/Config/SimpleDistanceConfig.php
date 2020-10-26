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

namespace CsvMigrations\FieldHandlers\Config;

/**
 * IntegerConfig
 *
 * This class provides the predefined configuration
 * for integer field handlers.
 */
class SimpleDistanceConfig extends FixedConfig
{
    /**
     * @var array $providers List of provider names and classes
     */
    protected $providers = [
        'combinedFields' => '\\CsvMigrations\\FieldHandlers\\Provider\\CombinedFields\\NullCombinedFields',
        'dbFieldType' => '\\CsvMigrations\\FieldHandlers\\Provider\\DbFieldType\\IntegerDbFieldType',
        'fieldValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldValue\\MixedFieldValue',
        'fieldToDb' => '\\CsvMigrations\\FieldHandlers\\Provider\\FieldToDb\\IntegerFieldToDb',
        'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\NumberSearchOperators',
        'searchOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOptions\\IntegerSearchOptions',
        'selectOptions' => '\\CsvMigrations\\FieldHandlers\\Provider\\SelectOptions\\NullSelectOptions',
        'renderInput' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderInput\\SimpleDistanceRenderer',
        'renderValue' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderValue\\SimpleDistanceRenderer',
        'renderName' => '\\CsvMigrations\\FieldHandlers\\Provider\\RenderName\\DefaultRenderer',
        'validationRules' => '\\CsvMigrations\\FieldHandlers\\Provider\\ValidationRules\\IntegerValidationRules',
    ];
}

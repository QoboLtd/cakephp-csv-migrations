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
namespace CsvMigrations\FieldHandlers\Provider\ValidationRules;

/**
 * AggregatedValidationRules
 *
 * This class provides the validation rules for the aggregated field type.
 */
class AggregatedValidationRules extends AbstractValidationRules
{
    /**
     * {@inheritDoc}
     */
    public function provide($validator = null, array $options = [])
    {
        return $validator;
    }
}

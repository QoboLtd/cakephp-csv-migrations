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
 * PhoneValidationRules
 *
 * This class provides the validation rules for the phone field type.
 */
class PhoneValidationRules extends AbstractValidationRules
{
    /**
     * Regex pattern for validation rule.
     */
    const VALIDATION_PATTERN = '^\+?[0-9\s\(\)\.\-]*$';

    /**
     * {@inheritDoc}
     */
    public function provide($validator = null, array $options = [])
    {
        $validator = parent::provide($validator, $options);
        $validator->regex($options['fieldDefinitions']->getName(), '/' . static::VALIDATION_PATTERN . '/');

        return $validator;
    }
}

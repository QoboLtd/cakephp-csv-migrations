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
 * EmailValidationRules
 *
 * This class provides the validation rules for the email field type.
 */
class EmailValidationRules extends AbstractValidationRules
{
    /**
     * {@inheritDoc}
     */
    public function provide($validator = null, array $options = [])
    {
        $validator = parent::provide($validator, $options);
        $validator->email($options['fieldDefinitions']->getName());

        return $validator;
    }
}

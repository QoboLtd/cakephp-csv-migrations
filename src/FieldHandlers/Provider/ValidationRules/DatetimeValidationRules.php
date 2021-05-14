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

use Cake\Validation\Validator;
use InvalidArgumentException;

/**
 * DatetimeValidationRules
 *
 * This class provides the validation rules for the datetime field type.
 */
class DatetimeValidationRules extends AbstractValidationRules
{
    /**
     * {@inheritDoc}
     */
    public function provide($validator = null, array $options = [])
    {
        if (! $validator instanceof Validator) {
            throw new InvalidArgumentException(
                sprintf('Validator parameter must be an instance of %s.', Validator::class)
            );
        }

        $field = $options['fieldDefinitions'];

        $fieldName = $field->getName();

        if ($field->getRequired()) {
            $validator->notEmptyDateTime($field->getName(), 'Missing datetime', 'create');
        } else {
            $validator->allowEmptyDateTime($field->getName());
        }

        $validator->dateTime($fieldName);

        return $validator;
    }
}

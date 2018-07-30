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
namespace CsvMigrations\FieldHandlers\Provider\Validation;

use Cake\Validation\Validator;
use CsvMigrations\FieldHandlers\Provider\AbstractProvider;
use InvalidArgumentException;

abstract class AbstractValidationRules extends AbstractProvider
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

        if ((bool)$field->getRequired()) {
            $validator->requirePresence($field->getName(), 'create');
            $validator->notBlank($field->getName());
        }

        if (! (bool)$field->getRequired()) {
            $validator->allowEmpty($field->getName());
        }

        return $validator;
    }
}

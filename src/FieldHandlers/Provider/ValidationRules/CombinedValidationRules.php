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

use CsvMigrations\FieldHandlers\FieldHandler;

/**
 * CombinedValidationRules
 *
 * This class provides the validation rules for the money field type.
 */
class CombinedValidationRules extends AbstractValidationRules
{
    /**
     * {@inheritDoc}
     */
    public function provide($validator = null, array $options = [])
    {
        $provider = $this->config->getProvider('combinedFields');
        $fields = (new $provider($this->config))->provide(null, $options);

        foreach ($fields as $suffix => $fieldOptions) {
            $fieldName = $this->config->getField() . '_' . $suffix;

            $config = new $fieldOptions['config']($fieldName, $this->config->getTable(), $options);

            $definitions = clone $options['fieldDefinitions'];
            $definitions->setName($fieldName);
            $validator = (new FieldHandler($config))->setValidationRules($validator, ['fieldDefinitions' => $definitions]);
        }

        return $validator;
    }
}

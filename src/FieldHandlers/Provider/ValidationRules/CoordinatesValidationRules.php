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
 * CoordinatesValidationRules
 *
 * CakePHP provide a native coordinate validation:
 * https://api.cakephp.org/3.4/class-Cake.Validation.Validation.html#_geoCoordinate
 * This class provides a more strict one, preventins spaces in the middle of the values.
 */
class CoordinatesValidationRules extends AbstractValidationRules
{
    /**
     * {@inheritDoc}
     */
    public function provide($validator = null, array $options = [])
    {
        $validator = parent::provide($validator, $options);

        $validator->add($options['fieldDefinitions']->getName(), 'validRegex', [
            'rule' => ['custom', '/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/Du'],
            'message' => 'The provided coordinates are invalid (ie: "-90.000000,54.123456")',
        ]);
        $validator->allowEmpty($options['fieldDefinitions']->getName());

        return $validator;
    }
}

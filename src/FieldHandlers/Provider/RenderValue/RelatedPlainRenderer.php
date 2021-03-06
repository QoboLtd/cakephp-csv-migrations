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

namespace CsvMigrations\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\RelatedFieldTrait;

/**
 * RelatedPlainRenderer
 *
 * Related value as plain
 */
class RelatedPlainRenderer extends AbstractRenderer
{
    use RelatedFieldTrait;

    /**
     * Provide rendered value
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $result = null;

        if (empty($data)) {
            return 'N/A';
        }

        if ('related' !== $options['fieldDefinitions']->getType()) {
            return $data;
        }

        $relatedProperties[] = $this->_getRelatedProperties($options['fieldDefinitions']->getLimit(), $data);

        if (!empty($relatedProperties[0]['config']['parent']['module'])) {
            array_unshift(
                $relatedProperties,
                $this->_getRelatedParentProperties($relatedProperties[0])
            );
        }

        $inputs = [];
        foreach ($relatedProperties as $properties) {
            if (empty($properties)) {
                continue;
            }

            $inputs[] = $properties['dispFieldVal'];
        }

        if (!empty($inputs)) {
            $result .= implode(' ' . $this->_separator . ' ', $inputs);
        }

        return $result;
    }
}

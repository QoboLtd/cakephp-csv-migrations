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
 * RelatedRenderer
 *
 * Related value as a linkable URL
 */
class RelatedRenderer extends AbstractRenderer
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
            return $result;
        }

        $relatedProperties[] = $this->_getRelatedProperties($options['fieldDefinitions']->getLimit(), $data);

        if (!empty($relatedProperties[0]['config']['parent']['module'])) {
            array_unshift(
                $relatedProperties,
                $this->_getRelatedParentProperties($relatedProperties[0])
            );
        }

        $elementName = 'CsvMigrations.FieldHandlers/RelatedFieldHandler/view';
        $params = [
            'relatedProperties' => $relatedProperties,
            'options' => $options,
        ];

        return $this->renderElement($elementName, $params);
    }
}

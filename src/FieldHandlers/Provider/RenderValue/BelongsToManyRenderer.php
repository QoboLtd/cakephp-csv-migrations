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

use Cake\ORM\TableRegistry;
use CsvMigrations\FieldHandlers\RelatedFieldTrait;

/**
 * BelongsToManyRenderer
 *
 * ManyToMany relation value as a linkable URL
 */
class BelongsToManyRenderer extends AbstractRenderer
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
        $relatedProperties = $this->_getRelatedProperties($options['fieldDefinitions']->getLimit(), (string)$data);

        $table = $this->config->getTable();
        $entity = $options['entity'];
        $association = $options['association'];
        $contactsData = TableRegistry::getTableLocator()->get($entity->getSource())->get($entity->get('id'), ['contain' => $association->getName()]);
        $associationData = $contactsData[$association->getProperty()];
        $associationList = [];
        if (!empty($associationData)) {
            foreach ($associationData as $associationRow) {
                $associationList[$associationRow->get('id')] = $associationRow->get($relatedProperties['displayField']);
            }
        }

        if (empty($associationList)) {
            return $data;
        }

        $elementName = 'CsvMigrations.FieldHandlers/BelongsToManyFieldHandler/view';
        $params = [
            'relatedProperties' => $relatedProperties,
            'options' => $options,
            'associationList' => $associationList
        ];

        return $this->renderElement($elementName, $params);
    }
}

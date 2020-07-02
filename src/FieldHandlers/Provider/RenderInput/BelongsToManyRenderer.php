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

namespace CsvMigrations\FieldHandlers\Provider\RenderInput;

use Cake\ORM\TableRegistry;
use CsvMigrations\FieldHandlers\RelatedFieldTrait;

/**
 * BelongsToManyRenderer
 *
 * BelongsToMany renderer provides the functionality
 * for rendering BelongsToMany inputs.
 */
class BelongsToManyRenderer extends AbstractRenderer
{
    use RelatedFieldTrait;

    /**
     * Provide
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $field = $this->config->getField();
        $table = $this->config->getTable();

        $fieldName = $table->aliasField($field);

        $relatedProperties = $this->_getRelatedProperties($options['fieldDefinitions']->getLimit(), (string)$data);
        if (!empty($relatedProperties['dispFieldVal']) && !empty($relatedProperties['config']['parent']['module'])) {
            $relatedParentProperties = $this->_getRelatedParentProperties($relatedProperties);
            if (!empty($relatedParentProperties['dispFieldVal'])) {
                $relatedProperties['dispFieldVal'] = implode(' ' . $this->_separator . ' ', [
                    $relatedParentProperties['dispFieldVal'],
                    $relatedProperties['dispFieldVal'],
                ]);
            }
        }

        $entity = $options['entity'] ?? null;
        $entityClass = $table->getEntityClass();
        $isNew = !(is_object($entity) && $entity instanceof $entityClass);
        $optionsSelected = [];
        if (!$isNew) {
            $association = $options['association'];
            $contactsData = TableRegistry::getTableLocator()->get($entity->getSource())->get($entity->get('id'), ['contain' => $association->getName()]);
            $associationData = $contactsData[$association->getProperty()];
            if (!empty($associationData)) {
                foreach ($associationData as $associationRow) {
                    $optionsSelected[$associationRow->get('id')] = $associationRow->get($relatedProperties['displayField']);
                }
            }
        }

        $params = [
            'field' => $field,
            'name' => $fieldName,
            'association' => $options['association'],
            'type' => 'select',
            'label' => isset($options['label']) ? $options['label'] : $options['association']->className(),
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => array_keys($optionsSelected),
            'options' => $optionsSelected,
            'relatedProperties' => $relatedProperties,
            'embedded' => !empty($options['emDataTarget']) ? $options['emDataTarget'] : $field,
            'icon' => $this->_getInputIcon($relatedProperties),
            'title' => $this->_getInputHelp($relatedProperties),
            'extraClasses' => (!empty($options['extraClasses']) ? implode(' ', $options['extraClasses']) : ''),
            'attributes' => empty($options['attributes']) ? [] : $options['attributes'],
            'help' => (!empty($options['help']) ? $options['help'] : ''),
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/BelongsToManyFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);
    }
}

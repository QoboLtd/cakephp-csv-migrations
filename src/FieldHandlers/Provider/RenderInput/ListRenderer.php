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

/**
 * ListRenderer
 *
 * List renderer provides the functionality
 * for rendering list inputs.
 */
class ListRenderer extends AbstractRenderer
{
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

        $selectOptions = ['' => ' -- Please choose -- '];

        // if select options are not pre-defined
        if (empty($options['selectOptions'])) {
            $selectListItems = $this->config->getProvider('selectOptions');
            $selectListItems = new $selectListItems($this->config);
            $listName = $options['fieldDefinitions']->getLimit();
            $listOptions = [];
            $selectOptions += $selectListItems->provide($listName, $listOptions);
        } else {
            $selectOptions += $options['selectOptions'];
        }

        $params = [
            'field' => $field,
            'name' => $fieldName,
            'type' => 'select',
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'options' => $selectOptions,
            'extraClasses' => (!empty($options['extraClasses']) ? implode(' ', $options['extraClasses']) : ''),
            'attributes' => empty($options['attributes']) ? [] : $options['attributes'],
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/ListFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);
    }
}

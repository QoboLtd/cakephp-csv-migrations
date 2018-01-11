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
 * CombinedRenderer
 *
 * Combined renderer provides the functionality
 * for rendering combined inputs.
 */
class CombinedRenderer extends AbstractRenderer
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
        $label = $options['label'];

        $combinedFields = $this->config->getProvider('combinedFields');
        $combinedFields = new $combinedFields($this->config);
        $combinedFields = $combinedFields->provide($data, $options);

        $view = $this->config->getView();

        $inputs = [];
        foreach ($combinedFields as $suffix => $preOptions) {
            // Skip individual inputs' label
            $options['label'] = false;
            $fieldName = $this->config->getField() . '_' . $suffix;

            $fieldData = $data;
            if (empty($fieldData) && !empty($options['entity'])) {
                $fieldData = $options['entity'];
            }

            $handler = new $preOptions['handler']($this->config->getTable(), $fieldName, $view);

            $inputs[] = $handler->renderInput($fieldData, $options);
        }

        $params = [
            'field' => $this->config->getField(),
            'label' => $label,
            'required' => $options['fieldDefinitions']->getRequired(),
            'inputs' => $inputs
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/CombinedFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);
    }
}

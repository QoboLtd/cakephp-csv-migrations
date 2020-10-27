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

use Cake\Core\Configure;

/**
 * DistanceSimple
 *
 * Integer renderer provides the functionality
 * for rendering integer inputs.
 */
abstract class AbstractSimpleUnitRenderer extends AbstractRenderer
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

        $type = $this->getCalledClass();
        $options['attributes']['inputmask'] = Configure::read('CsvMigrations.Inputmask.setup');
        $options['attributes']['inputmask']['prefix'] = Configure::read('CsvMigrations.Inputmask.' . $type . '.prefix', '') . ' ';
        $options['attributes']['inputmask']['suffix'] = ' ' . Configure::read('CsvMigrations.Inputmask.' . $type . '.suffix', '');
        $options['attributes']['inputmask']['digits'] = Configure::read('CsvMigrations.Inputmask.' . $type . '.digits', '0');

        $params = [
            'field' => $field,
            'name' => $fieldName,
            'type' => 'string',
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'extraClasses' => (!empty($options['extraClasses']) ? implode(' ', $options['extraClasses']) : ''),
            'attributes' => empty($options['attributes']) ? [] : $options['attributes'],
            'placeholder' => (!empty($options['placeholder']) ? $options['placeholder'] : ''),
            'help' => (!empty($options['help']) ? $options['help'] : ''),
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/SimpleUnitFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);
    }

    /**
     * Service function to extract called class name.
     *
     * @return string
     */
    private function getCalledClass(): string
    {
        $class = explode('\\', get_called_class());

        return (string)preg_replace(['/Simple/', '/Renderer/'], '', (string)end($class));
    }
}

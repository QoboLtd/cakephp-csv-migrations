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
namespace CsvMigrations\FieldHandlers\Provider\SearchOptions;

use Cake\ORM\TableRegistry;

/**
 * DblistSearchOptions
 *
 * Database List search options
 */
class DblistSearchOptions extends AbstractSearchOptions
{
    /**
     * Provide search options
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $field = $this->config->getField();

        $template = $this->getBasicTemplate('string');
        $defaultOptions = $this->getDefaultOptions($data, $options);
        $defaultOptions['input'] = ['content' => $template];

        $result[$this->config->getField()] = $defaultOptions;

        $list = $options['fieldDefinitions']->getListName();

        $table = TableRegistry::get('CsvMigrations.Dblists');

        $params = [
            'field' => $field,
            'name' => '{{name}}',
            'type' => 'select',
            'label' => false,
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => '{{value}}',
            'options' => $table->find('options', ['name' => $list])
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/DblistFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        $result[$field]['input'] = [
            'content' => $this->renderElement($element, $params)
        ];

        return $result;
    }
}

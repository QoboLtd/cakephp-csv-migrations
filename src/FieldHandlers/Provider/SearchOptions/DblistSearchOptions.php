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
use CsvMigrations\Model\Table\DblistsTable;
use Webmozart\Assert\Assert;

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

        $result[$field] = $defaultOptions;

        $list = $options['fieldDefinitions']->getListName();

        $table = TableRegistry::get('CsvMigrations.Dblists');
        Assert::isInstanceOf($table, DblistsTable::class);

        $selectOptions = $table->getOptions($list);

        $params = [
            'field' => $field,
            'name' => '{{name}}',
            'type' => 'select',
            'label' => false,
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => '{{value}}',
            'options' => $selectOptions,
            'extraClasses' => (!empty($options['extraClasses']) ? implode(' ', $options['extraClasses']) : ''),
            'attributes' => empty($options['attributes']) ? [] : $options['attributes'],
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/DblistFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        foreach ($selectOptions as $key => $value) {
            $result[$field]['options'][] = ['value' => $key, 'label' => $value];
        }
        $result[$field]['input'] = ['content' => $this->renderElement($element, $params)];

        return $result;
    }
}

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

/**
 * IntegerSearchOptions
 *
 * Integer search options
 */
class IntegerSearchOptions extends AbstractSearchOptions
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
        $template = $this->getBasicTemplate('number');
        $defaultOptions = $this->getDefaultOptions($data, $options);
        $defaultOptions['input'] = ['content' => $template];

        $view = $this->config->getView();
        $content = $view->Form->input('', [
            'name' => '{{name}}',
            'value' => '{{value}}',
            'type' => 'number',
            'step' => 1,
            'max' => '99999999999',
            'label' => false
        ]);

        $defaultOptions['input'] = [
            'content' => $content
        ];

        $result[$this->config->getField()] = $defaultOptions;

        return $result;
    }
}

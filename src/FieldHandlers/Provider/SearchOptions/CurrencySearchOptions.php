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

use CsvMigrations\FieldHandlers\Provider\RenderValue\CurrencyRenderer as CurrencyValueRenderer;
use CsvMigrations\FieldHandlers\Setting;

/**
 * CurrencySearchOptions
 *
 * Currency search options
 */
class CurrencySearchOptions extends AbstractSearchOptions
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

        $template = $this->getBasicTemplate('text');

        $defaultOptions = $this->getDefaultOptions($data, $options);
        $defaultOptions['input'] = ['content' => $template];

        $result[$field] = $defaultOptions;

        $selectListItems = $this->config->getProvider('selectOptions');
        $selectListItems = new $selectListItems($this->config);
        $listName = $options['fieldDefinitions']->getLimit();
        $selectOptions = ['' => Setting::EMPTY_OPTION_LABEL()];
        $optionList = $selectListItems->provide($listName, []);
        foreach ($optionList as $k => $v) {
            $selectOptions[$k] = CurrencyValueRenderer::getIcon($k, $v);
        }

        $view = $this->config->getView();

        $attributes = [
            'class' => 'form-control',
            'label' => false,
            'data-class' => 'select2',
        ];

        $content = $view->Form->select('{{name}}', $selectOptions, $attributes);

        $result[$field]['source'] = $options['fieldDefinitions']->getLimit();
        $result[$field]['input'] = [
            'content' => $content,
            'post' => [
                [
                    'type' => 'script',
                    'content' => [
                        'CsvMigrations.dom-observer',
                        'AdminLTE./bower_components/select2/dist/js/select2.full.min',
                        'CsvMigrations.select2.init'
                    ],
                    'block' => 'scriptBottom'
                ],
                [
                    'type' => 'css',
                    'content' => [
                        'AdminLTE./bower_components/select2/dist/css/select2.min',
                        'Qobo/Utils.select2-bootstrap.min',
                        'Qobo/Utils.select2-style'
                    ],
                    'block' => 'css'
                ]
            ]
        ];

        return $result;
    }
}

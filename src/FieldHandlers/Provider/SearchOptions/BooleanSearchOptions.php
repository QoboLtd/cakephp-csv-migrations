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
 * BooleanSearchOptions
 *
 * Boolean search options
 */
class BooleanSearchOptions extends AbstractSearchOptions
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
        $defaultOptions = $this->getDefaultOptions($data, $options);

        $view = $this->config->getView();
        $template = $view->Form->input('{{name}}', [
            'type' => 'checkbox',
            'class' => 'square',
            'label' => false
        ]);

        $defaultOptions['input'] = [
            'content' => $template,
            'post' => [
                [
                    'type' => 'script',
                    'content' => [
                        'CsvMigrations.dom-observer',
                        'AdminLTE./plugins/iCheck/icheck.min',
                        'CsvMigrations.icheck.init'
                    ],
                    'block' => 'scriptBottom'
                ],
                [
                    'type' => 'css',
                    'content' => 'AdminLTE./plugins/iCheck/all',
                    'block' => 'css'
                ]
            ]
        ];

        $result[$this->config->getField()] = $defaultOptions;

        return $result;
    }
}

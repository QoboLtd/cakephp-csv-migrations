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
 * DatetimeSearchOptions
 *
 * Datetime search options
 */
class DatetimeSearchOptions extends AbstractSearchOptions
{
    /**
     * Custom form input templates.
     *
     * @var array Associative array of templates
     */
    protected $templates = [
        'input' => '<div class="input-group %s">
            <div class="input-group-addon">
                <i class="fa fa-%s"></i>
            </div>
            <input type="{{type}}" name="{{name}}"{{attrs}}/>
        </div>'
    ];

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
        if (isset($options['element'])) {
            $template = $view->element($options['element'], [
                'options' => [
                    'fieldName' => '{{name}}',
                    'value' => '{{value}}',
                    'type' => 'datetimepicker',
                    'label' => false
                ]
            ]);
        } else {
            $template = $view->Form->input('', [
                'name' => '{{name}}',
                'value' => '{{value}}',
                'type' => 'text',
                'data-provide' => 'datetimepicker',
                'autocomplete' => 'off',
                'label' => false,
                'templates' => [
                    'input' => vsprintf($this->templates['input'], [
                        '',
                        'calendar'
                    ])
                ]
            ]);
        }

        $defaultOptions['input'] = [
            'content' => $template,
            'post' => [
                [
                    'type' => 'script',
                    'content' => [
                        'CsvMigrations.dom-observer',
                        'AdminLTE./plugins/daterangepicker/moment.min',
                        'AdminLTE./plugins/daterangepicker/daterangepicker',
                        'CsvMigrations.datetimepicker.init'
                    ],
                    'block' => 'scriptBottom'
                ],
                [
                    'type' => 'css',
                    'content' => 'AdminLTE./plugins/daterangepicker/daterangepicker',
                    'block' => 'css'
                ]
            ]
        ];

        $result[$this->config->getField()] = $defaultOptions;

        return $result;
    }
}
